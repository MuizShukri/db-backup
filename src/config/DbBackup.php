<?php 

/**
 * Configuration for the database backup process.
 * 
 * Specify database connection, tables to exclude, extra options for dumping,
 * file naming conventions, backup directory, Google Drive integration,
 * and logging details.
 */
return [
    'database' => [
        'connection'    => env('DB_BACKUP_CONNECTION', 'mysql'),
        'exclude_tables' => [
        ],
        'extra_options' => [
            '--single-transaction',
            '--quick',
            '--routines',
            '--events',
            '--skip-lock-tables',
        ],
    ],
    'file_prefix'              => strtolower(env('APP_NAME')),
    'backup_directory'         => storage_path('app' . DIRECTORY_SEPARATOR . 'db_backups'),
    'keep_backup_count'        => 1,
    'google_drive_credentials' => env('GOOGLE_DRIVE_CREDENTIALS'),
    'google_drive_folder_id'   => env('GOOGLE_DRIVE_FOLDER_ID'),
    'logging' => [
        'channel' => 'dbbackup', 
        'level'   => 'info',
        'path'    => storage_path('logs/dbbackup.log'),
    ],
];
