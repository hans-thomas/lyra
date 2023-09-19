<?php

namespace Hans\Lyra;

use Illuminate\Support\ServiceProvider;

class LyraServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('lyra-service', LyraService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'lyra');

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerPublishes();
        }
    }

    /**
     * Register created commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->commands([
            // commands register here
        ]);
    }

    /**
     * Register publishable files.
     *
     * @return void
     */
    private function registerPublishes()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('lyra.php'),
        ], 'lyra-config');
    }
}
