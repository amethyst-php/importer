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
     *
     * @return Result
     */
    public function import(Importer $importer, File $file)
    {
        $result = new Result();

        $mimeType = $file->getMedia()[0]->mime_type;

        $classes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ImportXlsxFile::class,
            'text/csv'                                                          => ImportCsvFile::class,
        ];

        if (!isset($classes[$mimeType])) {
            $result->addErrors(Collection::make(new \Exception('Invalid MimeType')));
        }

        if ($result->ok()) {
            $className = $classes[$mimeType];
            dispatch(new $className($importer, $file, $this->getAgent()));
        }

        return $result;
    }
}
