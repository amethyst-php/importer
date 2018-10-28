<?php

namespace Railken\Amethyst\Providers;

use Railken\Amethyst\Common\CommonServiceProvider;

class ImporterServiceProvider extends CommonServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        parent::register();
        $this->app->register(\Railken\Amethyst\Providers\DataBuilderServiceProvider::class);
        $this->app->register(\Railken\Amethyst\Providers\FileServiceProvider::class);
    }
}
