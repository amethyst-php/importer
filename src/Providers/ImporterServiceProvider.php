<?php

namespace Railken\Amethyst\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Railken\Amethyst\Api\Support\Router;
use Railken\Amethyst\Common\CommonServiceProvider;
use Railken\Amethyst\Console\Commands\ImporterSeed;

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
        $this->app->register(\Railken\Amethyst\Providers\DataBuilderServiceProvider::class);
        $this->app->register(\Railken\Amethyst\Providers\FileServiceProvider::class);
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
