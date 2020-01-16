<?php

declare(strict_types=1);

namespace App\Service;

use League\Csv\Reader;

class CsvFileReader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param String $filePath
     * @return Reader
     */
    public function read(String $filePath): Reader
    {
        return $this->reader = Reader::createFromPath($filePath);
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }
}
