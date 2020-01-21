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
     * @var CSVFileValidator
     */
    private $csvFileValidation;

    /**
     * @var int
     */
    public $totalCount;

    /**
     * @var int
     */
    public $processedCount;

    /**
     * @var int
     */
    public $skippedCount;

    /**
     * @var \ArrayObject
     */
    public $products;

    /**
     * @param EntityManagerInterface $em
     * @param \App\Service\CsvFileReader $csvFileReader
     * @param \App\Service\CSVFileValidator $csvFileValidation
     */
    public function __construct(
        EntityManagerInterface $em,
        CsvFileReader $csvFileReader,
        CSVFileValidator $csvFileValidation
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
        $reader = $this->csvFileReader->read($path);
        $results = $reader->fetchAssoc();
        $this->totalCount = iterator_count($results);

        if ($this->csvFileValidation->validate($results)) {
            $this->importToDatabase($results, $isTest);
        }
    }

    /**
     * @param \Iterator $results
     * @param bool $isTest
     */
    private function importToDatabase(\Iterator $results, Bool $isTest): void
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

                $this->processedCount++;
            }
        }
        $this->skippedCount = $this->totalCount - $this->processedCount;

    }

    /**
     * @param array $row
     * @return bool
     */
    private function checkRequirements(Array $row): bool
    {
        return $row['Cost in GBP'] > 5 && $row['Cost in GBP'] <= 1000 && $row['Stock'] > 10;
    }
}

