<?php

namespace Davitig\Ima;

use Curl\Curl;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ImaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::configPath() => $this->app['path.config'] . DIRECTORY_SEPARATOR . 'ima.php'
            ], 'config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(self::configPath(), 'ima');

        $this->app->singleton(Ima::class, function ($app) {
            return new Ima(
                new Curl,
                new Repository($app['config']->get('ima')),
                $app['request']->ip()
            );
        });
    }

    /**
     * Get the config path
     *
     * @return string
     */
    public static function configPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ima.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [Ima::class];
    }
}
