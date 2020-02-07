<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    private $csvFileValidator;

    /**
     * @var int
     */
    private $totalCount = 0;

    /**
     * @var int
     */
    private $processedCount = 0;

    /**
     * @var int
     */
    private $skippedCount = 0;

    /**
     * @var \ArrayObject
     */
    private $products;

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    /**
     * @param int $processedCount
     */
    public function setProcessedCount(int $processedCount): void
    {
        $this->processedCount = $processedCount;
    }

    /**
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * @param int $skippedCount
     */
    public function setSkippedCount(int $skippedCount): void
    {
        $this->skippedCount = $skippedCount;
    }

    /**
     * @return \ArrayObject
     */
    public function getProducts(): \ArrayObject
    {
        return $this->products;
    }

    /**
     * @param \ArrayObject $products
     */
    public function setProducts(\ArrayObject $products): void
    {
        $this->products = $products;
    }


    /**
     * @param EntityManagerInterface $em
     * @param \App\Service\CsvFileReader $csvFileReader
     * @param \App\Service\CSVFileValidator $CSVFileValidator

     */
    public function __construct(
        EntityManagerInterface $em,
        CsvFileReader $csvFileReader,
        CSVFileValidator $CSVFileValidator
    ) {
        $this->em = $em;
        $this->csvFileReader = $csvFileReader;
        $this->csvFileValidator = $CSVFileValidator;
        $this->products = new \ArrayObject();
    }

    /**
     * @param String $path
     * @param bool $isTest
     */
    public function importProducts(String $path, Bool $isTest)
    {
        $reader = $this->csvFileReader->read($path);
        $results = $reader->fetchAssoc();
        $column = $reader->fetchOne();

        if ($this->csvFileValidator->validateColumns($column)) {
            if ($this->csvFileValidator->validate($results)) {
                $this->totalCount = iterator_count($results);
                $this->importToDatabase($results, $isTest);
            }
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
                $existingCategory = $this->em->getRepository(Category::class)->findOneBy([
                    'name' => $row['Category']
                ]);

                if (!$existingCategory) {
                    $category = (new Category())
                        ->setName($row['Category']);
                    $this->em->persist($category);
                } else {
                    $category = $existingCategory;
                }

                $product = (new Product())
                    ->setProductCode($row['Product Code'])
                    ->setProductName($row['Product Name'])
                    ->setProductDescription($row['Product Description'])
                    ->setStock((int)$row['Stock'])
                    ->setCost((int)$row['Cost in GBP'])
                    ->setDiscontinued($row['Discontinued'])
                    ->setCategory($category);

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

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->csvFileValidator->getErrorMessages();
    }

    public function resetErrors()
    {
        $this->csvFileValidator->setErrorMessages([]);
    }

    public function resetProductCounters()
    {
        $this->products = new \ArrayObject();
        $this->totalCount = 0;
        $this->skippedCount = 0;
        $this->processedCount = 0;
        $this->resetErrors();
    }
}

