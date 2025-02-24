<?php 

namespace Moistcake\DbBackup\Helpers;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class GoogleDriveHelper
{
    protected static function client()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path(env('GOOGLE_DRIVE_CREDENTIALS')));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        // $client->setDeveloperKey(env('GOOGLE_DRIVE_API_KEY'));

        return $client;
    }

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

    public static function checkDriveFileAccess()
    {
        $client = self::client();
        $service = new Drive($client);

        try {
            $results = $service->files->listFiles([
                'pageSize' => 1,
                'fields' => 'files(id, name)'
            ]);

            if (count($results->getFiles()) > 0) {
                echo "Connected to Google Drive. Example file: " . $results->getFiles()[0]->getName();
            } else {
                echo "Connected, but no files found in Google Drive.";
            }
        } catch (\Exception $e) {
            echo "Failed to access Google Drive: " . $e->getMessage();
        }
    }

    public static function uploadFile($filePath, $fileName, $folderId = null)
    {
        $client = self::client();
        $service = new Drive($client);
    
        $fileMetadata = ['name' => $fileName];
        if ($folderId) {
            $fileMetadata['parents'] = [$folderId];
        }
    
        $file = new DriveFile($fileMetadata);
    
        $chunkSizeBytes = 262144;
        $client->setDefer(true);
        $request = $service->files->create($file, ['fields' => 'id', 'supportsAllDrives' => true]);
    
        $media = new \Google\Http\MediaFileUpload(
            $client,
            $request,
            mime_content_type($filePath),
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));
    
        $handle = fopen($filePath, "rb");
        while (!$media->nextChunk($handle));
        fclose($handle);
        $client->setDefer(false);
    
        return "https://drive.google.com/file/d/{$media->getMediaFile()->id}/view";
    }
    
}