<?php

namespace Jonas\TestPackage;

use Illuminate\Support\ServiceProvider;

class TestPackageServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (is_dir(__DIR__ . '/../resources/lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'jonas');
        }

        if (is_dir(__DIR__ . '/../resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'jonas');
        }

        if (is_dir(__DIR__ . '/../database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        if (file_exists(__DIR__ . '/../routes.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        }

        // Register event listeners
        $this->registerEventListeners();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    protected function registerEventListeners(): void
    {
        $events = $this->app['events'];

        if (config('test-package.listen_auth_events', true)) {
            $events->listen(
                \Illuminate\Auth\Events\Login::class,
                \Jonas\TestPackage\Listeners\LogUserLogin::class
            );

            $events->listen(
                \Illuminate\Auth\Events\Logout::class,
                \Jonas\TestPackage\Listeners\LogUserLogout::class
            );

            $events->listen(
                \Illuminate\Auth\Events\Registered::class,
                \Jonas\TestPackage\Listeners\LogUserRegistered::class
            );
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/test-package.php', 'test-package');

        // Register the service the package provides.
        // $this->app->singleton('test-package', function ($app) {
        //     return new TestPackage;
        // });
        $this->app->singleton('test-package', concrete: function ($app) {
            return new ActivityLogger(config('test-package'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['test-package'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/test-package.php' => config_path('test-package.php'),
        ], 'test-package.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/jonas'),
        ], 'test-package.views');*/

        // Publishing assets.
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/jonas'),
        ], 'test-package.assets');

        // Publishing the translation files.
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/jonas'),
        ], 'test-package.lang');

        // Registering package commands.
        $this->commands([
            Commands\TestCommand::class,
            Commands\ActivityLogCommand::class,
        ]);
    }
}
