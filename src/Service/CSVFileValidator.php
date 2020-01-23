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
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function isCorrectNumericField($value, Int $rowNumber, String $fieldName): void
    {
        if ($value == "") {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "Empty value"));
        } elseif (!is_numeric($value)) {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "String value instead number"));
        }
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function isCorrectStringField($value, Int $rowNumber, String $fieldName): void
    {
        if ($value == "") {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "Empty value"));
        } elseif (!$this->isCorrectStringLength($value)) {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "String length greater then 255"));
        } elseif (preg_match('/^\d+$/', $value)) {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "Numeric value provided instead string"));
        }
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function isCorrectProductCodeField($value, Int $rowNumber, String $fieldName): void
    {
        if ($value == "") {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "Empty value"));
        } elseif (!$this->isCorrectStringLength($value)) {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "String length greater then 255"));
        } elseif (!preg_match('/^P\d+/', $value)) {
            array_push($this->errorMessages,
                $this->buildErrorMessage($value, $rowNumber, $fieldName, "String must begin from 'P' character"));
        }
    }

    /**
     * @param String $value
     * @return bool
     */
    private function isCorrectStringLength(String $value): bool
    {
        return strlen($value) <= 255;
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     * @param String $error
     * @return String
     */
    private function buildErrorMessage($value, Int $rowNumber, String $fieldName, String $error): String
    {
        $rowNumber++;
        return sprintf("Line number: %s | Column: %s | Value in column: %s | Error: %s\n",
            $rowNumber, $fieldName, $value, $error);
    }
}

