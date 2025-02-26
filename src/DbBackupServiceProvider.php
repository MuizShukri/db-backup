<?php

namespace Moistcake\DbBackup;

use Illuminate\Support\ServiceProvider;
use Moistcake\DbBackup\Commands\DatabaseBackup;

class DbBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db-backup.php' => config_path('db-backup.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseBackup::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db-backup.php',
            'db-backup'
        );

        $this->app->make('config')->set('logging.channels.dbbackup', [
            'driver' => 'single',
            'path'   => config('db-backup.logging.path'),
            'level'  => config('db-backup.logging.level', 'info'),
        ]);
    }
}