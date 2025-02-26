<?php

namespace Moistcake\DbBackup;

use Illuminate\Support\ServiceProvider;
use Moistcake\DbBackup\Commands\DatabaseBackup;

class DbBackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DbBackup.php' => config_path('DbBackup.php'),
        ], 'dbbackup-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseBackup::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DbBackup.php',
            'DbBackup'
        );

        $this->app->make('config')->set('logging.channels.dbbackup', [
            'driver' => 'single',
            'path'   => config('DbBackup.logging.path'),
            'level'  => config('DbBackup.logging.level', 'info'),
        ]);
    }
}

