<?php

namespace Moistcake\DbBackup\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Carbon\Carbon;
use Moistcake\DbBackup\Helpers\GoogleDriveHelper;
use Illuminate\Support\Facades\Log;

class DatabaseBackup extends Command
{
    protected $signature = 'moistcake:db-backup';
    protected $description = 'Backup database to Google Drive';

    public function handle()
    {
        $backupDir = config('DbBackup.backup_directory');
        $fileName = config('DbBackup.file_prefix') . "_db_backup_" . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $folderId = config('DbBackup.google_drive_folder_id');

        // create directory if not exist
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        Log::channel(config('DbBackup.logging.channel'))->info("---------------------------------------------------");
        Log::channel(config('DbBackup.logging.channel'))->info('Starting Database Backup...');

        try {
            // backup database
            $this->backupDatabase($backupDir, $fileName, $filePath);

            // upload file to google drive
            Log::channel(config('DbBackup.logging.channel'))->info('Uploading Database to Google Drive...');

            $uploadStartTime = microtime(true);
            $fileUrl = GoogleDriveHelper::uploadFile($filePath, $fileName, $folderId);
            $uploadEndTime = microtime(true);

            $uploadTime = number_format($uploadEndTime - $uploadStartTime);

            Log::channel(config('DbBackup.logging.channel'))->info("{$fileUrl}");
            Log::channel(config('DbBackup.logging.channel'))->info("Database upload completed in {$uploadTime} seconds.");

            // remove old backups
            $this->removeOldBackups($backupDir);

        } catch (\Exception $e) {
            Log::channel(config('DbBackup.logging.channel'))->error("Database backup failed: {$e->getMessage()}");
        }
    }

    /**
     * Backup database to the given file path.
     * 
     * @param string $backupDir The directory to store the backup file.
     * @param string $fileName The name of the backup file.
     * @param string $filePath The path of the backup file.
     * 
     * @return void
     */
    private function backupDatabase($backupDir, $fileName, $filePath)
    {
        $connection = config('DbBackup.database.connection');
        $dbConfig = config("database.connections.{$connection}");

        $backupStartTime = microtime(true);

        MySql::create()
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password'])
            ->setHost($dbConfig['host'])
            ->addExtraOption(implode(' ', config('DbBackup.database.extra_options')))
            ->excludeTables(config('DbBackup.database.exclude_tables'))
            ->dumpToFile($filePath);

        $backupEndTime = microtime(true);

        $backupTime = number_format($backupEndTime - $backupStartTime);
        Log::channel(config('DbBackup.logging.channel'))->info("Database backup completed in {$backupTime} seconds.");
        Log::channel(config('DbBackup.logging.channel'))->info("---------------------------------------------------");
    }

    /**
     * Remove old backups until the number of backups is equal to the given count.
     * 
     * @param string $backupDir The directory of the backups.
     * 
     * @return void
     */
    private function removeOldBackups($backupDir)
    {
        if (is_dir($backupDir)) {
            $files = array_diff(scandir($backupDir), ['.', '..']);
            $fileCount = count($files);
            if ($fileCount > config('DbBackup.keep_backup_count')) {
                asort($files);
                unlink($backupDir . DIRECTORY_SEPARATOR . array_shift($files));
            }
        }
    }
}
