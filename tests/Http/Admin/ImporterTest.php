<?php

namespace Railken\Amethyst\Tests\Http\Admin;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;

use Railken\Amethyst\Api\Support\Testing\TestableBaseTrait;
use Railken\Amethyst\Fakers\ImporterFaker;
use Railken\Amethyst\Tests\BaseTest;
use Railken\Amethyst\Fakers\DataBuilderFaker;
use Railken\Amethyst\Managers\DataBuilderManager;
use Railken\Amethyst\Managers\FileManager;
use Railken\Amethyst\Managers\ImporterManager;
use Railken\Amethyst\Tests\DataBuilders\UserDataBuilder;

class ImporterTest extends BaseTest
{
    use TestableBaseTrait;

    /**
     * Faker class.
     *
     * @var string
     */
    protected $faker = ImporterFaker::class;

    /**
     * Router group resource.
     *
     * @var string
     */
    protected $group = 'admin';

    /**
     * Route name.
     *
     * @var string
     */
    protected $route = 'admin.importer';

    public function testImportXlsx()
    {
        $path = $this->getTempFile('file.xlsx');

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($path);
        $writer->addRows([
            ['id', 'name', 'email', 'password'],
            [1, 'test-1', 'test-1@test.net', 'password'],
            [2, 'test-2', 'test-2@test.net', 'password'],
        ]);

        $writer->close();


        $response = $this->callAndTest('POST', route('admin.file.create'), ['file' => base64_encode(file_get_contents($path))], 201);
        $content = json_decode($response->getContent());
        $file = $content->data;

        $dbm = new DataBuilderManager();
        $dataBuilder = $dbm->createOrFail(DataBuilderFaker::make()->parameters()
            ->set('name', 'User By Id')
            ->set('class_name', UserDataBuilder::class)
            ->set('input', [
                'id' => [
                    'type'       => 'integer',
                    'validation' => 'integer',
                ],
            ])
            ->set('filter', 'id eq "{{ id }}"')
        )->getResource();

        $im = new ImporterManager();
        $importer = $im->create(ImporterFaker::make()->parameters()
            ->remove('data_builder')
            ->set('data_builder_id', $dataBuilder->id)
            ->set('data', [
                'id'       => '{{ record.id }}',
                'name'     => '{{ record.name }}',
                'email'    => '{{ record.email }}',
                'password' => '{{ record.password }}',
            ])
        )->getResource();
        
        $response = $this->callAndTest('POST', route('admin.importer.import', ['importer_id' => $importer->id]), ['file_id' => $file->id], 200);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getTempFile(string $filename): string
    {
        $dir = __DIR__.'/../../var/cache';

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.'/'.$filename;
    }
}
