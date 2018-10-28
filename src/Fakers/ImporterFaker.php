<?php

namespace Railken\Amethyst\Fakers;

use Faker\Factory;
use Railken\Bag;
use Railken\Lem\Faker;

class ImporterFaker extends Faker
{
    /**
     * @return \Railken\Bag
     */
    public function parameters()
    {
        $faker = Factory::create();

        $bag = new Bag();
        $bag->set('name', $faker->name);
        $bag->set('description', $faker->text);
        $bag->set('data_builder', DataBuilderFaker::make()->parameters()->toArray());
        $bag->set('data', [
            'x' => '{{ record.x }}',
        ]);
        $bag->set('keys', [
            'primary' => ['x'],
        ]);

        return $bag;
    }
}
