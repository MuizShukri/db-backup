<?php 

namespace Moistcake\DbBackup\Helpers;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveHelper
{
    /**
     * Create and configure a Google API client.
     * 
     * @return Client
     */
    protected static function client()
    {
        // Create a Google API client
        $client = new Client();
        // Load the service account credentials from the file
        $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_CREDENTIALS')));
        // Set the scope of the client to access the Google Drive
        $client->addScope(Drive::DRIVE);
        // Set the access type to offline to allow the client to access the Google Drive even if the user is not signed in
        $client->setAccessType('offline');
        // This is optional, but recommended to set the developer key
        // $client->setDeveloperKey(env('GOOGLE_DRIVE_API_KEY'));

        return $client;
    }

    /**
     * Check the connection to Google Drive.
     * Outputs the connected user's display name and email if successful.
     */
    public static function checkGoogleDriveConnection()
    {
        // Create a Google API client
        $client = self::client();
        // Create a Google Drive service
        $service = new Drive($client);

        try {
            // Get the user information
            $about = $service->about->get(['fields' => 'user']);
            // Output the connected user's display name and email
            echo "Connected as: " . $about->getUser()->getDisplayName() . " (" . $about->getUser()->getEmailAddress() . ")";
        } catch (\Throwable $th) {
            // Output the error message if the connection failed
            echo "Connection failed: " . $th->getMessage();
        }
    }

    /**
     * Upload a file to Google Drive.
     * 
     * @param string $filePath The path to the file to upload.
     * @param string $fileName The name of the file in Google Drive.
     * @param string|null $folderId The ID of the folder to upload the file into.
     * 
     * @return string The URL to view the uploaded file.
     */
    public static function uploadFile($filePath, $fileName, $folderId = null)
    {
        // Create a Google API client
        $client = self::client();
        // Create a Google Drive service
        $service = new Drive($client);
    
        // Create a new file metadata
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => $folderId ? [$folderId] : null,
        ]);
    
        // Get the file size and MIME type
        $mimeType = mime_content_type($filePath);
        $fileSize = filesize($filePath);
        // Set the chunk size in bytes
        $chunkSizeBytes = config('dbbackup.google_drive_chunk_size');
    
        // Set the client to defer the request until the file is fully uploaded
        $client->setDefer(true);
    
        // Create a request to create a new file
        $request = $service->files->create($fileMetadata, [
            'mimeType' => $mimeType,
            'uploadType' => 'resumable',
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);
    
        // Create a media file upload
        $media = new MediaFileUpload(
            $client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        // Set the file size
        $media->setFileSize($fileSize);
    
        // Open the file in read binary mode
        $handle = fopen($filePath, 'rb');
    
        // Upload the file in chunks
        while (!feof($handle)) {
            // Read a chunk of the file
            $chunk = fread($handle, $chunkSizeBytes);
            // Upload the chunk
            $status = $media->nextChunk($chunk);
        }
    
        // Close the file
        fclose($handle);
        // Set the client to no longer defer the request
        $client->setDefer(false);
    
        // Return the URL to view the uploaded file
        return "https://drive.google.com/file/d/{$status->id}/view";
    }
}