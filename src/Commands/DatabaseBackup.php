<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Carbon\Carbon;
use App\Helpers\GoogleDriveHelper;
use Illuminate\Support\Facades\Log;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup database to Google Drive';

    public function handle()
    {
        $backupDir = config('db-backup.backup_directory');
        $fileName = config('db-backup.file_prefix') . "_db_backup_" . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $folderId = config('db-backup.google_drive_folder_id');

        // create directory if not exist
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        Log::channel(config('db-backup.logging.channel'))->info("---------------------------------------------------");
        Log::channel(config('db-backup.logging.channel'))->info('Starting Database Backup...');

        try {
            // backup database
            $backupStartTime = microtime(true);

            $connection = config('db-backup.database.connection');
            $dbConfig = config("database.connections.{$connection}");

            MySql::create()
                ->setDbName($dbConfig['database'])
                ->setUserName($dbConfig['username'])
                ->setPassword($dbConfig['password'])
                ->setHost($dbConfig['host'])
                ->addExtraOption(implode(' ', config('db-backup.database.extra_options')))
                ->excludeTables(config('db-backup.database.exclude_tables'))
                ->dumpToFile($filePath);

            $backupEndTime = microtime(true);

            $backupTime = number_format($backupEndTime - $backupStartTime);
            Log::channel(config('db-backup.logging.channel'))->info("Database backup completed in {$backupTime} seconds.");
            Log::channel(config('db-backup.logging.channel'))->info("---------------------------------------------------");

            // upload file to google drive
            Log::channel(config('db-backup.logging.channel'))->info('Uploading Database to Google Drive...');

            $uploadStartTime = microtime(true);
            $fileUrl = GoogleDriveHelper::uploadFile($filePath, $fileName, $folderId);
            $uploadEndTime = microtime(true);

            $uploadTime = number_format($uploadEndTime - $uploadStartTime);
            Log::channel(config('db-backup.logging.channel'))->info("{$fileUrl}");
            Log::channel(config('db-backup.logging.channel'))->info("Database uploaded in {$uploadTime} seconds");
            Log::channel(config('db-backup.logging.channel'))->info("---------------------------------------------------");

            // remove old backups
            if (is_dir($backupDir)) {
                $files = array_diff(scandir($backupDir), ['.', '..']);
                $fileCount = count($files);
                if ($fileCount > config('db-backup.keep_backup_count')) {
                    asort($files);
                    unlink($backupDir . DIRECTORY_SEPARATOR . array_shift($files));
                }
            }

        } catch (\Exception $e) {
            Log::channel(config('db-backup.logging.channel'))->error("Database backup failed: {$e->getMessage()}");
        }
    }
}