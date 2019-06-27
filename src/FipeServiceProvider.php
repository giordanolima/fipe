<?php

namespace GiordanoLima\Fipe;

use Illuminate\Support\ServiceProvider;

class FipeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $this->app->bind('fipe', function($app) {
            return new Fipe($app);
        });
    }

    /**
     * Register the configuration.
     */
    private function handleConfigs()
    {
        // $configPath = __DIR__.'/../config/fipe.php';
        // $this->publishes([$configPath => config_path('fipe.php')]);
        // $this->mergeConfigFrom($configPath, 'fipe');
    }

}
