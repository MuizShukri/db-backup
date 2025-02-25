
# Laravel Database Auto Backup to Google Drive

This package provides an automated solution for backing up your Laravel application's database to Google Drive. Regular backups are essential to safeguard your data against unexpected events, and integrating with Google Drive ensures your backups are securely stored in the cloud.




## Features

- Automated Backups: Schedule backups to run at your preferred intervals without manual intervention.
- Google Drive Integration: Seamlessly store your database backups in your Google Drive account.
- Easy Configuration: Simple setup process to get your backups running quickly.


## Installation

To install the package, use Composer:

```bash
composer require moistcake/db-backup
```

After installation, publish the configuration file:

```bash
php artisan vendor:publish --provider="moistcake\db-backup\src\DbBackupServiceProvider"
```

This command will create a db-backup.php configuration file in your config directory.


## Configuration

1. Google Drive Setup: Obtain your Google Drive API credentials and add them to your .env file:

```env
GOOGLE_DRIVE_CREDENTIALS=google-service-acount-key-path
GOOGLE_DRIVE_FOLDER_ID=google-drive-folder-id
```

2. Database Backup Settings: In the config/db-backup.php file, configure the following options:

- backup_frequency: Define how often backups should occur (e.g., daily, weekly).
- backup_time: Set the time of day when backups should run.
- databases: List the databases you want to back up.


## Usage

Once configured, the package will handle automatic backups based on your settings. However, you can also initiate a manual backup using the Artisan command:

```bash
php artisan db:backup
```

This command will create a backup of your specified databases and upload them to your configured Google Drive folder.



## Scheduling

To automate the backup process, add the following entry to your application's App\Console\Kernel class:

```php
$schedule->command('db:backup')->dailyAt('02:00');
```

Adjust the dailyAt time to your preferred backup time.
## Restoration

To restore a backup, download the desired backup file from your Google Drive and use your database client to import the SQL file. Ensure you have the necessary permissions and that the target database is prepared for the import.
## License

This package is open-source software licensed under the [MIT](https://choosealicense.com/licenses/mit/) license.
