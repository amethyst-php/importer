<?php

namespace Amethyst\Console\Commands;

use Amethyst\Managers\DataBuilderManager;
use Amethyst\Managers\ImporterManager;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

class ImporterSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amethyst:importer:seed';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dataBuilderManager = new DataBuilderManager();
        $importerManager = new ImporterManager();

        $managers = app('amethyst')->getData()->map(function ($data) {
            return Arr::get($data, 'manager');
        });

        foreach ($managers as $classManager) {
            $dataBuilderRecord = $dataBuilderManager->getRepository()->findOneBy([
                'name' => (new $classManager())->getName().' by id',
            ]);

            $dataBuilder = $dataBuilderRecord->newInstanceData();

            $importerManager->updateOrCreateOrFail([
                'name' => (new $classManager())->getName().' by id',
            ], [
                'data_builder_id' => $dataBuilderRecord->id,
                'data'            => Yaml::dump($dataBuilder->getManager()->getAttributes()->mapWithKeys(function ($attribute) use ($dataBuilder) {
                    return [$attribute->getName() => '{{ record.'.$attribute->getName().' }}'];
                })->toArray()),
            ]);
        }
    }
}
