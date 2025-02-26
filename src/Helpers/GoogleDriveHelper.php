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
    protected static function client()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path(config('db-backup.google_drive_credentials')));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');
        return $client;
    }

    public static function uploadFile($filePath, $fileName, $folderId = null)
    {
        $client = self::client();
        $service = new Drive($client);

        $fileMetadata = ['name' => $fileName];
        if ($folderId) $fileMetadata['parents'] = [$folderId];

        $file = new DriveFile($fileMetadata);
        $stream = Utils::streamFor(fopen($filePath, 'r'));

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