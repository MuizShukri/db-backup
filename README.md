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
php artisan vendor:publish --provider="Moistcake\\DbBackup\\DbBackupServiceProvider" --tag="dbbackup-config"
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
php artisan moistcake:backup
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
[2024-02-26 12:00:00] local.INFO: Database backup started.
[2024-02-26 12:01:00] local.INFO: Database backup completed in 60 seconds.
[2024-02-26 12:01:10] local.INFO: File uploaded to Google Drive: https://drive.google.com/file/d/abc123/view
```

---

## ⚡ Google Drive Connection Check

To test the Google Drive connection:

```php
use Moistcake\\DbBackup\\Helpers\\GoogleDriveHelper;

GoogleDriveHelper::checkGoogleDriveConnection();
```

---

## 🛠 Configuration Options

`config/db-backup.php`:

```php
return [
    'database' => [
        'connection'    => env('DB_BACKUP_CONNECTION', 'mysql'),
        'exclude_tables' => [
            // what table to exclude
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
    'backup_directory'         => storage_path('app'. DIRECTORY_SEPARATOR .'db_backups'),
    'keep_backup_count'        => 1,
    'google_drive_credentials' => env('GOOGLE_DRIVE_CREDENTIALS'),
    'google_drive_folder_id'   => env('GOOGLE_DRIVE_FOLDER_ID'),
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

