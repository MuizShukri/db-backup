<?php

namespace Moistcake\DbBackup;

use Illuminate\Support\ServiceProvider;
use Moistcake\DbBackup\Commands\DatabaseBackup;

class DbBackupServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->commands([
            DatabaseBackup::class, // Register the command
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // You can publish config or do additional setup here if needed
    }
}
