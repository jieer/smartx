<?php

namespace Smartwell;

use Illuminate\Support\ServiceProvider;

class SmartwellServicesProvider extends ServiceProvider
{

    protected $commands = [
        Console\InstallCommand::class,
    ];

    protected $routeMiddleware = [
        'jieer.jwt'       => Middleware\SmartWellJWTAuth::class,
    ];
    protected $middlewareGroups = [];
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/smartwell.php' => config_path('smartwell.php'),]);
        }

    }

    public function register()
    {
        $this->registerRouteMiddleware();

        $this->commands($this->commands);

    }

    protected function registerRouteMiddleware()
    {
        foreach ($this->middlewareGroups as $key => $middleware) {
            $this->app['mini']->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->app['mini']->aliasMiddleware($key, $middleware);
        }
    }
}