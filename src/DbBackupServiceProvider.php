<?php

namespace Moistcake\DbBackup;

use Illuminate\Support\ServiceProvider;
use Moistcake\DbBackup\Commands\DatabaseBackup;

/**
 * Service provider for the Laravel DbBackup package.
 */
class DbBackupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Publish the configuration file for the package.
        // This allows the user to configure the package.
        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DbBackup.php' => config_path('dbbackup.php'),
        ], 'dbbackup-config');

        // If the application is running in the console, register the command.
        if ($this->app->runningInConsole()) {
            $this->commands([
                DatabaseBackup::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge the configuration file for the package with the application's configuration.
        // This allows the user to configure the package.
        $this->mergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DbBackup.php',
            'DbBackup'
        );

        // Set the logging configuration for the package.
        // This allows the user to configure the logging for the package.
        $this->app->make('config')->set('logging.channels.dbbackup', [
            'driver' => 'single',
            'path'   => config('dbbackup.logging.path'),
            'level'  => config('dbbackup.logging.level', 'info'),
        ]);
    }
}