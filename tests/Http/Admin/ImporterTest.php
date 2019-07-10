<?php

namespace Amethyst\Tests\Http\Admin;

use Amethyst\Api\Support\Testing\TestableBaseTrait;
use Amethyst\Fakers\DataBuilderFaker;
use Amethyst\Fakers\ImporterFaker;
use Amethyst\Managers\DataBuilderManager;
use Amethyst\Managers\ImporterManager;
use Amethyst\Tests\BaseTest;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Yaml\Yaml;

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

    public function testHttpImportXlsx()
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

        $response = $this->callAndTest('POST', route('admin.file.create'), [], 201);
        $content = json_decode($response->getContent());
        $file = $content->data;
        $response = $this->call('POST', route('admin.file.upload', [$file->id]), [
            'file' => new UploadedFile($path, 'text.xlsx', null, null, null, true),
        ]);

        $dbm = new DataBuilderManager();
        $dataBuilder = $dbm->createOrFail(
            DataBuilderFaker::make()->parameters()
                ->set('name', 'User By Id')
                ->set('class_name', \Amethyst\DataBuilders\CommonDataBuilder::class)
                ->set('class_arguments', Yaml::dump(\Amethyst\Managers\UserManager::class))
                ->set('input', Yaml::dump([
                    'id' => [
                        'type'       => 'integer',
                        'validation' => 'integer',
                    ],
                ]))
                ->set('filter', 'id eq "{{ id }}"')
        )->getResource();

        $im = new ImporterManager();
        $importer = $im->create(
            ImporterFaker::make()->parameters()
                ->remove('data_builder')
                ->set('data_builder_id', $dataBuilder->id)
                ->set('data', Yaml::dump([
                    'id'       => '{{ record.id }}',
                    'name'     => '{{ record.name }}',
                    'email'    => '{{ record.email }}',
                    'password' => '{{ record.password }}',
                ]))
        )->getResource();

        $response = $this->callAndTest('POST', route('admin.importer.execute', ['importer_id' => $importer->id]), ['file_id' => $file->id, 'type' => 'xlsx'], 200);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getTempFile(string $filename): string
    {
        $dir = __DIR__.'/../../../var/cache';

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.'/'.$filename;
    }
}
