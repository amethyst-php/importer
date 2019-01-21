<?php

namespace Railken\Amethyst\Managers;

use Illuminate\Support\Collection;
use Railken\Amethyst\Common\ConfigurableManager;
use Railken\Amethyst\Jobs\ImportCsvFile;
use Railken\Amethyst\Jobs\ImportXlsxFile;
use Railken\Amethyst\Models\File;
use Railken\Amethyst\Models\Importer;
use Railken\Lem\Manager;
use Railken\Lem\Result;

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
        $result = new Result();

        $classes = [
            'xlsx' => ImportXlsxFile::class,
            'csv'  => ImportCsvFile::class,
        ];

        if (!isset($classes[$type])) {
            $result->addErrors(Collection::make([new \Exception('Invalid MimeType')]));
        }

        if ($result->ok()) {
            $className = $classes[$type];
            dispatch(new $className($importer, $file, $this->getAgent()));
        }

        return $result;
    }
}
