<?php

namespace Mawuekom\ModelUuid;

use Illuminate\Support\ServiceProvider;

class ModelUuidServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        require_once __DIR__.'/helpers.php';
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        
    }
}
