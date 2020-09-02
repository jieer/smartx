<?php

namespace Smartwell;

use Illuminate\Support\ServiceProvider;

class SmartwellServicesProvider extends ServiceProvider
{

    protected $commands = [
        Console\InstallCommand::class,
    ];

    protected $routeMiddleware = [
        'jieer.app'       => Middleware\WxAppHandle::class,
        'jieer.jwt'       => Middleware\SmartWellJWTAuth::class,
    ];
    protected $middlewareGroups = [
        'jieer' => [
            'jieer.app',
            'jieer.jwt',
        ],
    ];

    public function boot()
    {
        $this->registerPublishing();
    }

    public function register()
    {
        $this->registerRouteMiddleware();

        $this->commands($this->commands);

    }

    protected function registerRouteMiddleware()
    {
        foreach ($this->middlewareGroups as $key => $middleware) {
            $this->app['router']->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->app['router']->aliasMiddleware($key, $middleware);
        }
    }

    protected  function registerPublishing() {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/smartwell.php' => config_path('smartwell.php')], 'jieer-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'jieer-migrations');
        }
    }
}