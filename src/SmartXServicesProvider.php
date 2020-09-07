<?php

namespace SmartX;

use Illuminate\Support\ServiceProvider;

class SmartXServicesProvider extends ServiceProvider
{

    protected $commands = [
        Console\InstallCommand::class,
    ];

    protected $routeMiddleware = [
        'smartx.app'       => Middleware\WxAppHandle::class,
        'smartx.jwt'       => Middleware\SmartXWellJWTAuth::class,
    ];
    protected $middlewareGroups = [
        'smartx' => [
            'smartx.app',
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
            $this->publishes([__DIR__ . '/../config/smartx.php' => config_path('smartx.php')], 'smartx-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'smartx-migrations');
        }
    }
}