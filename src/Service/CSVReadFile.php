<?php
namespace App\Service;
use League\Csv\Reader;

class CSVReadFile
{
  private $reader;

  function __construct()
  {
    // code...
  }

  public function read(String $filePath)
  {
    return $this->reader = Reader::createFromPath($filePath);
  }

  public function getReader() {
    return $this->reader;
  }
}
