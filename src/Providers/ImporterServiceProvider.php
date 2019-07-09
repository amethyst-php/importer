<?php

namespace Amethyst\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Amethyst\Api\Support\Router;
use Amethyst\Common\CommonServiceProvider;
use Amethyst\Console\Commands\ImporterSeed;

class ImporterServiceProvider extends CommonServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        parent::register();
        $this->commands([ImporterSeed::class]);
        $this->loadExtraRoutes();
        $this->app->register(\Amethyst\Providers\DataBuilderServiceProvider::class);
        $this->app->register(\Amethyst\Providers\FileServiceProvider::class);
    }

    /**
     * Load extras routes.
     */
    public function loadExtraRoutes()
    {
        $config = Config::get('amethyst.importer.http.admin.importer');
        if (Arr::get($config, 'enabled')) {
            Router::group('admin', Arr::get($config, 'router'), function ($router) use ($config) {
                $controller = Arr::get($config, 'controller');
                $router->post('/{id}/execute', ['as' => 'execute', 'uses' => $controller.'@execute']);
            });
        }
    }
}
