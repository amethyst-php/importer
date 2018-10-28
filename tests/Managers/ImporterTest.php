<?php

namespace Railken\Amethyst\Tests\Managers;

use Railken\Amethyst\Fakers\ImporterFaker;
use Railken\Amethyst\Managers\ImporterManager;
use Railken\Amethyst\Tests\BaseTest;
use Railken\Lem\Support\Testing\TestableBaseTrait;

class ImporterTest extends BaseTest
{
    use TestableBaseTrait;

    /**
     * Manager class.
     *
     * @var string
     */
    protected $manager = ImporterManager::class;

    /**
     * Faker class.
     *
     * @var string
     */
    protected $faker = ImporterFaker::class;
}
