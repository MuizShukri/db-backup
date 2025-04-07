<?php 

/**
 * Configuration for the database backup process.
 * 
 * This configuration file specifies the database connection settings, tables
 * to exclude from the backup, additional options for the database dump,
 * naming conventions for backup files, storage directories, integration 
 * with Google Drive, and logging details.
 */
return [
    'database' => [
        // Specifies the database connection to use for backups
        'connection' => env('DB_BACKUP_CONNECTION', 'mysql'),

        // Tables to exclude from the backup to reduce size and time
        'exclude_tables' => [
        ],

        // Extra options to pass to the database dump command
        'extra_options' => [
            '--single-transaction',
            '--quick',
            '--routines',
            '--events',
            '--skip-lock-tables',
        ],
    ],

    // Prefix for naming backup files
    'file_prefix' => strtolower(env('APP_NAME')),

    // Directory where backups will be stored locally
    'backup_directory' => storage_path('app' . DIRECTORY_SEPARATOR . 'db_backups'),

    // Number of backup files to keep before deleting the oldest ones
    'keep_backup_count' => 2,

    // Credentials for Google Drive API for uploading backups
    'google_drive_credentials' => env('GOOGLE_DRIVE_CREDENTIALS'),

    // Folder ID in Google Drive where backups will be stored
    'google_drive_folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),

    // Size of chunks to use when uploading to Google Drive
    'google_drive_chunk_size' => 262144, // 256KB

    'logging' => [
        // Logging channel to use for backup logs
        'channel' => 'dbbackup', 

        // Logging level for backup operations
        'level' => 'info',

        // Path to the log file for backup processes
        'path' => storage_path('logs/dbbackup.log'),
    ],
];