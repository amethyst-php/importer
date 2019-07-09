<?php

namespace Amethyst\Tests\Managers;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Amethyst\Fakers\DataBuilderFaker;
use Amethyst\Fakers\ImporterFaker;
use Amethyst\Managers\DataBuilderManager;
use Amethyst\Managers\FileManager;
use Amethyst\Managers\ImporterManager;
use Amethyst\Models\User;
use Amethyst\Tests\BaseTest;
use Railken\Lem\Support\Testing\TestableBaseTrait;
use Symfony\Component\Yaml\Yaml;

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

    public function testImportXlsx()
    {
        $path = $this->getTempFile('file.xlsx');

        $data = $this->getData();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($path);

        $writer->addRows([array_keys($data[0])]);
        $writer->addRows($data);
        $writer->close();

        $this->commonImport($path, $data, 'xlsx');
    }

    public function testImportJson()
    {
        $path = $this->getTempFile('file.json');

        $data = $this->getData();

        file_put_contents($path, json_encode($data));

        $this->commonImport($path, $data, 'json');
    }

    public function getData()
    {
        return [
            [
                'id'       => 1,
                'name'     => str_random(40),
                'email'    => 'test-1@test.net',
                'password' => 'password',
            ],
            [
                'id'       => 2,
                'name'     => str_random(40),
                'email'    => 'test-2@test.net',
                'password' => 'password',
            ],
        ];
    }

    public function commonImport($path, $data, $type)
    {
        $fm = new FileManager();
        $file = $fm->create([])->getResource();
        $result = $fm->uploadFileByContent($file, file_get_contents($path));

        $this->assertTrue($result->ok());

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

        $importer = $this->getManager()->create(
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

        $result = $this->getManager()->import($importer, $file, $type);

        $this->assertTrue($result->ok());
        foreach ($data as $row) {
            $this->assertEquals($row['name'], User::find($row['id'])->name);
        }
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
