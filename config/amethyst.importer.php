<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    |
    | Here you can change the table name and the class components.
    |
    */
    'data' => [
        'importer' => [
            'table'      => 'amethyst_importers',
            'comment'    => 'Importer',
            'model'      => Railken\Amethyst\Models\Importer::class,
            'schema'     => Railken\Amethyst\Schemas\ImporterSchema::class,
            'repository' => Railken\Amethyst\Repositories\ImporterRepository::class,
            'serializer' => Railken\Amethyst\Serializers\ImporterSerializer::class,
            'validator'  => Railken\Amethyst\Validators\ImporterValidator::class,
            'authorizer' => Railken\Amethyst\Authorizers\ImporterAuthorizer::class,
            'faker'      => Railken\Amethyst\Fakers\ImporterFaker::class,
            'manager'    => Railken\Amethyst\Managers\ImporterManager::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Http configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the routes
    |
    */
    'http' => [
        'admin' => [
            'importer' => [
                'enabled'    => true,
                'controller' => Railken\Amethyst\Http\Controllers\Admin\ImportersController::class,
                'router'     => [
                    'as'     => 'importer.',
                    'prefix' => '/importers',
                ],
            ],
        ],
    ],
];
