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
        $backupDir = config('dbbackup.backup_directory');
        $fileName = config('dbbackup.file_prefix') . "_db_backup_" . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $folderId = config('dbbackup.google_drive_folder_id');

        // create directory if not exist
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $databaseName = config('database.connections.' . config('dbbackup.database.connection') . '.database');

        Log::channel(config('dbbackup.logging.channel'))->info("Database Name: {$databaseName}");
        Log::channel(config('dbbackup.logging.channel'))->info('Starting Database Backup...');

        try {
            // backup database
            $this->backupDatabase($backupDir, $fileName, $filePath);

            // upload file to google drive
            Log::channel(config('dbbackup.logging.channel'))->info('Uploading Database to Google Drive...');

            $uploadStartTime = microtime(true);
            $fileUrl = GoogleDriveHelper::uploadFile($filePath, $fileName, $folderId);
            $uploadEndTime = microtime(true);

            $uploadTime = number_format($uploadEndTime - $uploadStartTime);

            Log::channel(config('dbbackup.logging.channel'))->info("{$fileUrl}");
            Log::channel(config('dbbackup.logging.channel'))->info("Database upload completed in {$uploadTime} seconds.");
            Log::channel(config('dbbackup.logging.channel'))->info("----------------------------------------------------------------------");

            // remove old backups
            $this->removeOldBackups($backupDir);

        } catch (\Exception $e) {
            Log::channel(config('dbbackup.logging.channel'))->error("Database backup failed: {$e->getMessage()}");
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
        $connection = config('dbbackup.database.connection');
        $dbConfig = config("database.connections.{$connection}");

        // start timer
        $backupStartTime = microtime(true);

        // backup database
        MySql::create()
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password'])
            ->setHost($dbConfig['host'])
            ->addExtraOption(implode(' ', config('dbbackup.database.extra_options')))
            ->excludeTables(config('dbbackup.database.exclude_tables'))
            ->dumpToFile($filePath);

        // end timer
        $backupEndTime = microtime(true);

        // calculate time taken
        $backupTime = number_format($backupEndTime - $backupStartTime);
        Log::channel(config('dbbackup.logging.channel'))->info("Database backup completed in {$backupTime} seconds.");
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

            // Full paths with filemtime for sorting
            $filePaths = array_map(function ($file) use ($backupDir) {
                return $backupDir . DIRECTORY_SEPARATOR . $file;
            }, $files);

            // Sort by file modified time ascending (oldest first)
            usort($filePaths, function ($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });

            // Delete extra files if more than allowed
            $keepCount = config('dbbackup.keep_backup_count');
            if (count($filePaths) > $keepCount) {
                $filesToDelete = array_slice($filePaths, 0, count($filePaths) - $keepCount);
                foreach ($filesToDelete as $file) {
                    unlink($file);
                }
            }
        }
    }
}