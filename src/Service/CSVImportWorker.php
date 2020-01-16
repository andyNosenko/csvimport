<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class CSVImportWorker
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CsvFileReader
     */
    private $csvReadFile;

    /**
     * @var CSVFileValidation
     */
    private $csvFileValidation;

    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $processed;

    /**
     * @var int
     */
    public $skipped;

    /**
     * @param EntityManagerInterface $em
     * @param \App\Service\CsvFileReader $csvReadFile
     * @param \App\Service\CSVFileValidation $csvFileValidation
     */
    public function __construct(
        EntityManagerInterface $em,
        CsvFileReader $csvReadFile,
        CSVFileValidation $csvFileValidation
    ) {
        $this->em = $em;
        $this->csvReadFile = $csvReadFile;
        $this->csvFileValidation = $csvFileValidation;
    }

    /**
     * @param String $path
     * @param bool $isTest
     */
    public function importProducts(String $path, Bool $isTest)
    {
        $this->csvReadFile->read($path);
        $validate = $this->csvFileValidation->validate();
        $results = $validate->fetchAssoc();
        $this->total = iterator_count($results);

        if (!$this->stopImportProducts()) {
            $this->importToDatabase($results, $isTest);
        }
    }

    /**
     * @param \Iterator $results
     * @param bool $isTest
     */
    public function importToDatabase(\Iterator $results, Bool $isTest): void
    {
        foreach ($results as $row) {
            if ($this->importProductRequirements($row)) {
                    $product = (new Product())
                        ->setProductCode($row['Product Code'])
                        ->setProductName($row['Product Name'])
                        ->setProductDescription($row['Product Description'])
                        ->setStock((int)$row['Stock'])
                        ->setCost((int)$row['Cost in GBP'])
                        ->setDiscontinued($row['Discontinued']);

                    $this->em->persist($product);
                    if (!$isTest) {
                        $this->em->flush();
                    }

                    $this->processed++;
                }
            }

        $this->skipped = $this->total - $this->processed;

    }

    /**
     * @return String
     */
    public function stopImportProducts()
    {
        return $this->csvFileValidation->errorMessage;
    }

    /**
     * @param $row
     * @return bool
     */
    public function importProductRequirements($row): bool
    {
        if ($row['Cost in GBP'] > 5 && $row['Stock'] > 10) {
            if ($row['Cost in GBP'] <= 1000) {
                return true;
            }
        }
        return false;
    }
}

