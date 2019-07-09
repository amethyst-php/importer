<?php

namespace Amethyst\Authorizers;

use Railken\Lem\Authorizer;
use Railken\Lem\Tokens;

class ImporterAuthorizer extends Authorizer
{
    /**
     * List of all permissions.
     *
     * @var array
     */
    protected $permissions = [
        Tokens::PERMISSION_CREATE => 'importer.create',
        Tokens::PERMISSION_UPDATE => 'importer.update',
        Tokens::PERMISSION_SHOW   => 'importer.show',
        Tokens::PERMISSION_REMOVE => 'importer.remove',
    ];
}
