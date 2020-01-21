<?php

declare(strict_types=1);

namespace App\Service;

class CSVFileValidator
{
    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @param \Iterator $data_fields
     * @return bool
     */
    public function validate(\Iterator $data_fields): bool
    {

        foreach ($data_fields as $key => $row) {
            $this->isCorrectProductCodeField($row['Product Code'], $key, "Product Code");
            $this->isCorrectStringField($row['Product Name'], $key, "Product Name");
            $this->isCorrectStringField($row['Product Description'], $key, "Product Description");
            $this->isCorrectNumericField($row['Stock'], $key, "Stock");
            $this->isCorrectNumericField($row['Cost in GBP'], $key, "Cost in GBP");
        }

        return empty($this->errorMessages);
    }

    /**
     * @param $field
     * @param Int $key
     * @param String $fieldName
     */
    private function isCorrectNumericField($field, Int $key, String $fieldName): void
    {
        if ($field == "") {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Empty value"));
        } elseif (!is_numeric($field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String value instead number"));
        }
    }

    /**
     * @param $field
     * @param Int $key
     * @param String $fieldName
     */
    private function isCorrectStringField($field, Int $key, String $fieldName): void
    {
        if ($field == "") {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Empty value"));
        } elseif (!$this->isCorrectStringLength($field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "String length greater then 255"));
        } elseif (preg_match('/^\d+$/', $field)) {
            array_push($this->errorMessages, $this->buildErrorMessage($field, $key, $fieldName, "Numeric value provided instead string"));
        }
    }

    /**
     * @param $field
     * @param Int $key
     * @param String $fieldName
     */
    private function isCorrectProductCodeField($field, Int $key, String $fieldName): void
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

