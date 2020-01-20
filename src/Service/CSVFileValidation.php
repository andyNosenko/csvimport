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
     * @var array
     */
    public $errorMessage = [];

    /**
     * @var bool
     */
    public $isStopped = false;

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

    public function validateDataFields(\Iterator $data_fields): void
    {
        foreach ($data_fields as $row) {
            $this->isCorrectProductCodeField($row['Product Code']);
            $this->isCorrectStringField($row['Product Name']);
            $this->isCorrectStringField($row['Product Description']);
            $this->isCorrectNumericField($row['Stock']);
            $this->isCorrectNumericField($row['Cost in GBP']);
        }

//        foreach ($this->errorMessage as $message) {
//            var_dump($message);
//        }
    }

    public function isCorrectNumericField($field): void
    {
        if ($field == "") {
            array_push($this->errorMessage, $field." Empty value\n");
            $this->isStopped = true;
        }
        elseif ((float)$field == 0 || (int)$field == 0) {
            array_push($this->errorMessage, $field." String value instead number\n");
            $this->isStopped = true;
        }
    }

    public function isCorrectStringField($field): void
    {
        if(preg_match('/^\d+$/', $field)) {
            array_push($this->errorMessage, $field." Numeric value provided instead string\n");
            $this->isStopped = true;
        }
    }

    public function isCorrectProductCodeField($field): void
    {
        if(!preg_match('/^[P]/', $field)) {
           array_push($this->errorMessage, $field." String must began from 'P' character\n");
           $this->isStopped = true;
        }
    }
}

