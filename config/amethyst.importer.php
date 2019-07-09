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
            'model'      => Amethyst\Models\Importer::class,
            'schema'     => Amethyst\Schemas\ImporterSchema::class,
            'repository' => Amethyst\Repositories\ImporterRepository::class,
            'serializer' => Amethyst\Serializers\ImporterSerializer::class,
            'validator'  => Amethyst\Validators\ImporterValidator::class,
            'authorizer' => Amethyst\Authorizers\ImporterAuthorizer::class,
            'faker'      => Amethyst\Fakers\ImporterFaker::class,
            'manager'    => Amethyst\Managers\ImporterManager::class,
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
                'controller' => Amethyst\Http\Controllers\Admin\ImportersController::class,
                'router'     => [
                    'as'     => 'importer.',
                    'prefix' => '/importers',
                ],
            ],
        ],
    ],
];
