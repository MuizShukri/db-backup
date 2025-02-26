<?php

namespace Moistcake\DbBackup\Commands;

use Illuminate\Console\Command;
use Spatie\DbDumper\Databases\MySql;
use Carbon\Carbon;
use Moistcake\DbBackup\Helpers\GoogleDriveHelper;

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

        if (!file_exists($backupDir)) mkdir($backupDir, 0777, true);

        $this->info("Starting Database Backup...");

        try {
            $dbConfig = config("database.connections." . config('db-backup.database.connection'));

            MySql::create()
                ->setDbName($dbConfig['database'])
                ->setUserName($dbConfig['username'])
                ->setPassword($dbConfig['password'])
                ->setHost($dbConfig['host'])
                ->addExtraOption(implode(' ', config('db-backup.database.extra_options')))
                ->excludeTables(config('db-backup.database.exclude_tables'))
                ->dumpToFile($filePath);

            $this->info("Database backup completed.");
            $this->info("Uploading Database to Google Drive...");

            $fileUrl = GoogleDriveHelper::uploadFile($filePath, $fileName, $folderId);
            $this->info("Uploaded: {$fileUrl}");

            $this->cleanup($backupDir);
        } catch (\Exception $e) {
            $this->error("Backup failed: {$e->getMessage()}");
        }
    }

    private function cleanup($backupDir)
    {
        $files = array_diff(scandir($backupDir), ['.', '..']);
        if (count($files) > config('db-backup.keep_backup_count')) {
            asort($files);
            unlink($backupDir . DIRECTORY_SEPARATOR . array_shift($files));
        }
    }
}