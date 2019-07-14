<?php

namespace Amethyst\Managers;

use Amethyst\Common\ConfigurableManager;
use Amethyst\Jobs\ImportCsvFile;
use Amethyst\Jobs\ImportJsonFile;
use Amethyst\Jobs\ImportXlsxFile;
use Amethyst\Models\File;
use Amethyst\Models\Importer;
use Illuminate\Support\Collection;
use Railken\Lem\Manager;
use Railken\Lem\Result;

/**
 * @method \Amethyst\Models\Importer newEntity()
 * @method \Amethyst\Schemas\ImporterSchema getSchema()
 * @method \Amethyst\Repositories\ImporterRepository getRepository()
 * @method \Amethyst\Serializers\ImporterSerializer getSerializer()
 * @method \Amethyst\Validators\ImporterValidator getValidator()
 * @method \Amethyst\Authorizers\ImporterAuthorizer getAuthorizer()
 */
class ImporterManager extends Manager
{
    use ConfigurableManager;

    /**
     * @var string
     */
    protected $config = 'amethyst.importer.data.importer';

    /**
     * Import a file.
     *
     * @param Importer $importer
     * @param File     $file
     * @param string   $type
     *
     * @return Result
     */
    public function import(Importer $importer, File $file, string $type)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'amethyst');
        file_put_contents($filePath, file_get_contents($file->downloadable()));

        return $this->importFromFilePath($importer, $filePath, $type);
    }

    /**
     * Import a file.
     *
     * @param Importer $importer
     * @param string   $filePath
     * @param string   $type
     *
     * @return Result
     */
    public function importFromFilePath(Importer $importer, string $filePath, string $type)
    {
        $result = new Result();

        $classes = [
            'xlsx' => ImportXlsxFile::class,
            'csv'  => ImportCsvFile::class,
            'json' => ImportJsonFile::class,
        ];

        if (!isset($classes[$type])) {
            $result->addErrors(Collection::make([new \Exception('Invalid MimeType')]));
        }

        if ($result->ok()) {
            $className = $classes[$type];

            dispatch(new $className($importer, $filePath, $this->getAgent()));
        }

        return $result;
    }

    /**
     * Describe extra actions.
     *
     * @return array
     */
    public function getDescriptor()
    {
        return [
            'actions' => [
                'importer',
            ],
        ];
    }
}
