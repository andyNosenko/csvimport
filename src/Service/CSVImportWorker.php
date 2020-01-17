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
    private $csvFileReader;

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
     * @var \ArrayObject
     */
    public $products;

    /**
     * @param EntityManagerInterface $em
     * @param \App\Service\CsvFileReader $csvFileReader
     * @param \App\Service\CSVFileValidation $csvFileValidation
     */
    public function __construct(
        EntityManagerInterface $em,
        CsvFileReader $csvFileReader,
        CSVFileValidation $csvFileValidation
    ) {
        $this->em = $em;
        $this->csvFileReader = $csvFileReader;
        $this->csvFileValidation = $csvFileValidation;
        $this->products = new \ArrayObject();
    }

    /**
     * @param String $path
     * @param bool $isTest
     */
    //TODO: Instead String path Reader object
    public function importProducts(String $path, Bool $isTest)
    {
        $results = null;
        if ($this->csvFileValidation->validate($this->csvFileReader->read($path))) {
            $results = $this->csvFileReader->getReader();
            $this->total = iterator_count($results->fetchAssoc());
        }
        if (!$this->stopImportProducts()) {
            $this->importToDatabase($results->fetchAssoc(), $isTest);
        }
    }

    /**
     * @param \Iterator $results
     * @param bool $isTest
     */
    public function importToDatabase(\Iterator $results, Bool $isTest): void
    {
        foreach ($results as $row) {
            if ($this->checkRequirements($row)) {
                    $product = (new Product())
                        ->setProductCode($row['Product Code'])
                        ->setProductName($row['Product Name'])
                        ->setProductDescription($row['Product Description'])
                        ->setStock((int)$row['Stock'])
                        ->setCost((int)$row['Cost in GBP'])
                        ->setDiscontinued($row['Discontinued']);

                    $this->em->persist($product);
                    $this->products->append($product);
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
    //TODO: Will be unused after importProducts() refactoring
    public function stopImportProducts()
    {
        return $this->csvFileValidation->errorMessage;
    }

    /**
     * @param array $row
     * @return bool
     */
    public function checkRequirements(Array $row): bool
    {
        return $row['Cost in GBP'] > 5 && $row['Cost in GBP'] <= 1000 && $row['Stock'] > 10;
    }
}

