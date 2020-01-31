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

    public function setErrorMessages($messages)
    {
        $this->errorMessages = $messages;
    }

    /**
     * @param \Iterator $data_fields
     * @return bool
     */
    public function validate(\Iterator $data_fields): bool
    {
        foreach ($data_fields as $key => $row) {
            $this->validateProductCodeField($row['Product Code'], $key, "Product Code");
            $this->validateStringField($row['Product Name'], $key, "Product Name");
            $this->validateStringField($row['Product Description'], $key, "Product Description");
            $this->validateNumericField($row['Stock'], $key, "Stock");
            $this->validateNumericField($row['Cost in GBP'], $key, "Cost in GBP");
            $this->validateCategoryField($row['Category'], $key, 'Category');
        }

        return empty($this->errorMessages);
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function validateNumericField($value, Int $rowNumber, String $fieldName): void
    {
        if ($this->isEmptyValue($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "Empty value"
                )
            );
        } elseif (!is_numeric($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "String value instead number"
                )
            );
        }
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function validateStringField($value, Int $rowNumber, String $fieldName): void
    {
        if ($this->isEmptyValue($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "Empty value"
                )
            );
        } elseif (!$this->isCorrectStringLength($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "String length greater then 255"
                )
            );
        } elseif (preg_match('/^\d+$/', $value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "Numeric value provided instead string"
                )
            );
        }
    }

    /**
     * @param $value
     * @param Int $rowNumber
     * @param String $fieldName
     */
    private function validateProductCodeField($value, Int $rowNumber, String $fieldName): void
    {
        if ($this->isEmptyValue($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "Empty value"
                )
            );
        } elseif (!$this->isCorrectStringLength($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "String length greater then 255"
                )
            );
        } elseif (!preg_match('/^P\d+/', $value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "String must begin from 'P' character"
                )
            );
        }
    }

    private function validateCategoryField($value, Int $rowNumber, String $fieldName): void
    {
        if ($this->isEmptyValue($value)) {
            array_push(
                $this->errorMessages,
                $this->buildErrorMessage(
                    $value,
                    $rowNumber,
                    $fieldName,
                    "Empty value"
                )
            );
        }
    }

    private function isEmptyValue($value): bool
    {
        return $value == "";
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

