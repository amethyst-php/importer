<?php

namespace Amethyst\Jobs;

use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Railken\Bag;

class ImportJsonFile extends ImportCommonFile implements ShouldQueue
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
        return new Bag(['filepath' => $filePath]);
    }

    /**
     * Read.
     *
     * @param mixed   $reader
     * @param Closure $callback
     */
    public function read($reader, Closure $callback)
    {
        foreach (json_decode(file_get_contents($reader->filepath)) as $index => $row) {
            $callback($row, $index);
        }
    }
}
