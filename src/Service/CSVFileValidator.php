<?php

declare(strict_types=1);

namespace App\Service;

use League\Csv\Reader;

class CSVFileValidator
{
    /**
     * @var CsvFileReader
     */
    private $csvFileReader;

    /**
     * @var array
     */
    private $errorMessages = [];

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
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

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
        foreach ($data_fields as $key => $row) {
            $this->isCorrectProductCodeField($row['Product Code'], $key, "Product Code");
            $this->isCorrectStringField($row['Product Name'], $key, "Product Name");
            $this->isCorrectStringField($row['Product Description'], $key, "Product Description");
            $this->isCorrectNumericField($row['Stock'], $key, "Stock");
            $this->isCorrectNumericField($row['Cost in GBP'], $key, "Cost in GBP");
        }

        foreach ($this->errorMessages as $message) {
            var_dump($message);
        }
    }

    public function isCorrectNumericField($field, $key, $fieldName): void
    {
        if ($field == "") {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Empty value"));
        } elseif (!is_numeric($field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String value instead number"));
        }
    }

    public function isCorrectStringField($field, $key, $fieldName): void
    {
        if ($field == "") {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Empty value"));
        } elseif (!$this->isCorrectStringLength($field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String length greater then 255"));
        } elseif (preg_match('/^\d+$/', $field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Numeric value provided instead string"));
        }
    }

    public function isCorrectProductCodeField($field, $key, $fieldName): void
    {
        if ($field == "") {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Empty value"));
        } elseif (!$this->isCorrectStringLength($field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String length greater then 255"));
        } elseif (!preg_match('/^P\d+/', $field)) {
           array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String must begin from 'P' character"));
        }
    }

    /**
     * @param String $field
     * @return bool
     */
    private function isCorrectStringLength(String $field): bool
    {
        return strlen($field) <= 255;
    }

    /**
     * @param $field
     * @param Int $key
     * @param String $fieldName
     * @param String $error
     * @return String
     */
    private function buildErrorMessage($field, Int $key, String $fieldName, String $error): String
    {
        $key++;
        return sprintf("Line number: %s | Column: %s | Value in column: %s | Error: %s\n", $key, $fieldName, $field, $error);
    }


}

