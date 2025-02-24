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
        $backupDir = storage_path('app/db_backup');
        $fileName = 'db_backup_' . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $this->info('Starting database backup...');

        try {
            MySql::create()
                ->setDbName(config('database.connections.mysql.database'))
                ->setUserName(config('database.connections.mysql.username'))
                ->setPassword(config('database.connections.mysql.password'))
                ->setHost(config('database.connections.mysql.host'))
                ->addExtraOption('--single-transaction')
                ->addExtraOption('--quick')
                ->addExtraOption('--routines')
                ->addExtraOption('--events')
                ->addExtraOption('--skip-lock-tables')
                ->dumpToFile($filePath);

            $this->info("Database backup successful: {$fileName}");

            $fileUrl = GoogleDriveHelper::uploadFile($filePath, $fileName, $folderId);
            $this->info("Data stored at Google Drive: {$fileUrl}");
        } catch (\Exception $e) {
            $this->error("Database backup failed: {$e->getMessage()}");
        }
    }
}