<?php

namespace Moistcake\DbBackup;

use Illuminate\Support\ServiceProvider;
use Moistcake\DbBackup\Commands\DatabaseBackup;

class DbBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/db-backup.php' => config_path('db-backup.php'),
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
            __DIR__ . '/config/db-backup.php',
            'db-backup'
        );
    }
}