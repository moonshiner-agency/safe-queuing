<?php

namespace Moonshiner\SafeQueuing;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class SafeQueuingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'safe-queuing');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'safe-queuing');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('safe-queuing.php'),
            ], 'config');

            if (!class_exists('CreateReservationsTable')) {

                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__ . '/../database/migrations/create_reservations_table.php.stub' =>
                    database_path('migrations/' . $timestamp . '_create_reservations_table.php'),
                ], 'migrations');
            }

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/safe-queuing'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/safe-queuing'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/safe-queuing'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'safe-queuing');

        // Register the main class to use with the facade
        $this->app->singleton('safe-queuing', function () {
            return new SafeQueuing;
        });

        // register some timeslot collection macros
        Collection::make($this->macros())
            ->reject(fn ($class, $macro) => Collection::hasMacro($macro))
            ->each(fn ($class, $macro) => Collection::macro($macro, app($class)()));
    }

    private function macros(): array
    {
        return [
            'onDay' => \Moonshiner\SafeQueuing\Macros\Date::class,
            'afterDate' => \Moonshiner\SafeQueuing\Macros\Start::class,
            'beforeDate' => \Moonshiner\SafeQueuing\Macros\End::class,
            'findSlot' => \Moonshiner\SafeQueuing\Macros\Find::class,
            'calendar' => \Moonshiner\SafeQueuing\Macros\Calendar::class,
            'paginate' => \Moonshiner\SafeQueuing\Macros\Paginate::class,
            'simplePaginate' => \Moonshiner\SafeQueuing\Macros\SimplePaginate::class,
        ];
    }
}
