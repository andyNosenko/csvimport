<?php

namespace App\Service;

use App\Service\CSVReadFile;

class CSVFileValidation
{
    /**
     * @var CSVReadFile
     */
    private $csvReadFile;

    private $errorMessage;

    private $validFieldsRule = ['Product Code', 'Product Name',
        'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    function __construct(CSVReadFile $csvReadFile)
    {
        $this->csvReadFile = $csvReadFile;
    }

    public function identical_fields($arrayA, $arrayB)
    {
        return $arrayA == $arrayB;
    }
    public function validate()
    {
        $results = $this->csvReadFile->getReader()->fetchOne();
        $csvFields = [];
        foreach ($results as $row) {
            array_push($csvFields, $row);
        }
        if ($this->identical_fields($this->validFieldsRule, $csvFields)) {
            return $this->csvReadFile->getReader();
        }

    }
}
