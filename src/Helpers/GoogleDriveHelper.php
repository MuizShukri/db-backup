<?php 

namespace Moistcake\DbBackup\Helpers;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

class GoogleDriveHelper
{
    /**
     * Create and configure a Google API client.
     * 
     * @return Client
     */
    protected static function client()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_CREDENTIALS')));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        // $client->setDeveloperKey(env('GOOGLE_DRIVE_API_KEY'));

        return $client;
    }

    /**
     * Check the connection to Google Drive.
     * Outputs the connected user's display name and email if successful.
     */
    public static function checkGoogleDriveConnection()
    {
        $client = self::client();
        $service = new Drive($client);

        try {
            $about = $service->about->get(['fields' => 'user']);
            echo "Connected as: " . $about->getUser()->getDisplayName() . " (" . $about->getUser()->getEmailAddress() . ")";
        } catch (\Throwable $th) {
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
        $client = self::client();
        $service = new Drive($client);

        $fileMetadata = ['name' => $fileName];

        if ($folderId) {
            $fileMetadata['parents'] = [$folderId];
        }

        $file = new DriveFile($fileMetadata);

        $stream = Utils::streamFor(fopen($filePath, 'r'));

        $request = new Request('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable');
        
        $media = new MediaFileUpload(
            $client,
            $request,
            mime_content_type($filePath),
            null,
            true,
            $stream->getSize()
        );
        
        $media->setFileSize($stream->getSize());

        ini_set('memory_limit','2048M');

        $uploadedFile = $service->files->create($file, [
            'data' => $stream,
            'mimeType' => mime_content_type($filePath),
            'uploadType' => 'resumable',
            'fields' => 'id',
            'supportsAllDrives' => true
        ]);

        return "https://drive.google.com/file/d/{$uploadedFile->id}/view";
    }
}
