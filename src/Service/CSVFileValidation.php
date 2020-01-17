<?php

declare(strict_types=1);

namespace App\Service;

use League\Csv\Reader;

class CSVFileValidation
{
    /**
     * @var CsvFileReader
     */
    private $csvFileReader;

    /**
     * @var String
     */
    public $errorMessage;

    /**
     * @var array
     */
    private $validFieldsRule = [
        'Product Code',
        'Product Name',
        'Product Description',
        'Stock',
        'Cost in GBP',
        'Discontinued'
    ];

    /**
     * @param \App\Service\CsvFileReader $csvFileReader
     */
    function __construct(CsvFileReader $csvFileReader)
    {
        $this->csvFileReader = $csvFileReader;
    }

    /**
     * @param array $arrayA
     * @param array $arrayB
     * @return bool
     */
    public function identical_fields(Array $arrayA, Array $arrayB): bool
    {
        return $arrayA == $arrayB;
    }

    /**
     * @param Reader $reader
     * @return bool
     */
    public function validate(Reader $reader): bool
    {
        $results = $reader->fetchOne();
        $csvFields = [];

        foreach ($results as $row) {
            array_push($csvFields, $row);
        }

        if ($this->identical_fields($this->validFieldsRule, $csvFields)) {
            return true;
        }
        return false;
    }
}

