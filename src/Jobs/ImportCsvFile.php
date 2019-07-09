<?php

namespace Amethyst\Jobs;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportCsvFile extends ImportXlsxFile implements ShouldQueue
{
    /**
     * Retrieve a generic reader.
     *
     * @param string $filePath
     *
     * @return mixed
     */
    public function getReader(string $filePath)
    {
        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($filePath);

        return $reader;
    }
}
