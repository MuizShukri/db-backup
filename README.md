# Laravel Database Backup

**Laravel Database Backup** is a Laravel package that provides an easy way to back up your MySQL database and upload the backup file directly to Google Drive.

## 🚀 Features

- MySQL database backup using `spatie/db-dumper`
- Direct upload to Google Drive using `google/apiclient`
- Configurable backup directory, file prefix, and folder ID
- Exclude specified tables from backup
- Retains a specified number of local backups

---

## 📦 Installation

Require the package via Composer:

```bash
composer require moistcake/db-backup
```

---

## ⚙️ Configuration

1. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag=dbbackup-config
```

2. **Set the following environment variables in your `.env` file:**

```ini
DB_BACKUP_CONNECTION=mysql
GOOGLE_DRIVE_CREDENTIALS=path/to/credentials.json
GOOGLE_DRIVE_FOLDER_ID=your_google_drive_folder_id
```

3. **Customize `config/db-backup.php` as needed.**

---

## 💡 Usage

Run the backup command:

```bash
php artisan moistcake:db-backup
```

The command will:
- Back up the database
- Upload the backup file to Google Drive
- Retain only the number of local backups specified in the config

---

## 📝 Logging Details

The package uses Laravel's logging system to provide detailed information about:

- Backup initiation and completion times
- Upload status and file URL on Google Drive
- Error messages in case of failures

**Example log entries:**

```
[2025-04-07 16:02:43] local.INFO: ----------------------------------------------------------------------  
[2025-04-07 16:02:47] local.INFO: Database Name: database_name  
[2025-04-07 16:02:47] local.INFO: Starting Database Backup...  
[2025-04-07 16:02:48] local.INFO: Database backup completed in 6 seconds.  
[2025-04-07 16:02:48] local.INFO: Uploading Database to Google Drive...  
[2025-04-07 16:02:59] local.INFO: https://drive.google.com/file/d/abc123/view  
[2025-04-07 16:02:59] local.INFO: Database upload completed in 360 seconds.  
[2025-04-07 16:02:59] local.INFO: ----------------------------------------------------------------------  
```

---

## 🛠 Configuration Options

`config/db-backup.php`:

```php
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
```

---

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

## 🤝 Contributing

Feel free to submit issues or pull requests. Contributions are welcome!

---

## 🙌 Credits

- [Spatie/db-dumper](https://github.com/spatie/db-dumper)
- [Google/apiclient](https://github.com/googleapis/google-api-php-client)

---

## 📧 Contact

For inquiries or support, open an issue or contact the maintainer at [muizshukri06@gmail.com].

