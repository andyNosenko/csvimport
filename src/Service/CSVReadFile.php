<?php
namespace App\Service;
use League\Csv\Reader;

class CSVReadFile
{

  function __construct()
  {
    // code...
  }

  public function read(String $filePath)
  {
    return $reader = Reader::createFromPath($filePath);
  }
}
