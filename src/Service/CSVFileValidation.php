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


  public function validate()
  {
    $results = $this->csvReadFile->getReader()->fetchOne();

    foreach ($results as $row) {

      if($row != "Product Code") {
        $this->setErrorMessage("Product Code field not found!");
      }

      break;
    }
    return $this->csvReadFile->getReader();
  }
}
