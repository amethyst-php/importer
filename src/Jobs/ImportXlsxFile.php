<?php

namespace Amethyst\Jobs;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportXlsxFile extends ImportCommonFile implements ShouldQueue
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
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($filePath);

        return $reader;
    }

    /**
     * Read.
     *
     * @param mixed   $reader
     * @param Closure $callback
     */
    public function read($reader, Closure $callback)
    {
        $head = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    $head = $row;

                    continue;
                }

                $callback(array_combine($head, $row), $rowIndex);
            }
        }
    }
}
