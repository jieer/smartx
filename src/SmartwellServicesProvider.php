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
        $this->loadMigration();
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/smartwell.php' => config_path('smartwell.php'),]);
        }

    }

    public function register()
    {
        $this->registerRouteMiddleware();

        $this->commands($this->commands);

    }

    public function  loadMigration() {
        if($this->app->runningInConsole()){
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
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
}