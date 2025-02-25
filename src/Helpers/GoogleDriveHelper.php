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