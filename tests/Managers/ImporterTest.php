<?php

namespace Railken\Amethyst\Tests\Managers;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Railken\Amethyst\Fakers\DataBuilderFaker;
use Railken\Amethyst\Fakers\ImporterFaker;
use Railken\Amethyst\Managers\DataBuilderManager;
use Railken\Amethyst\Managers\FileManager;
use Railken\Amethyst\Managers\ImporterManager;
use Railken\Amethyst\Tests\BaseTest;
use Railken\Amethyst\Tests\DataBuilders\UserDataBuilder;
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

        $fm = new FileManager();
        $result = $fm->uploadFileByContent(file_get_contents($path));

        $this->assertTrue($result->ok());

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

        $importer = $this->getManager()->create(ImporterFaker::make()->parameters()
            ->remove('data_builder')
            ->set('data_builder_id', $dataBuilder->id)
            ->set('data', [
                'id'       => '{{ record.id }}',
                'name'     => '{{ record.name }}',
                'email'    => '{{ record.email }}',
                'password' => '{{ record.password }}',
            ])
        )->getResource();

        $result = $this->getManager()->import($importer, $result->getResource(), 'xlsx');

        $this->assertTrue($result->ok());
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
