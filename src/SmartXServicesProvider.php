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
        'smartx.jwt'       => Middleware\SmartXJWTAuth::class,
    ];
    protected $middlewareGroups = [
        'smartx' => [
            'smartx.app',
        ],
    ];

    public function boot()
    {
        if (file_exists($routes = base_path('routes/'.config('smartx.auth_guard').'.php'))) {
            $this->loadRoutesFrom($routes);
        }
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