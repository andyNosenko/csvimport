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

        if ($this->csvFileValidator->validate($results)) {
            $this->totalCount = iterator_count($results);
            $this->importToDatabase($results, $isTest);
            $this->removeFile($path);
        }
    }

    /**
     * @param String $path
     * @return bool
     */
    public function isFileValid(String $path):bool
    {
        $reader = $this->csvFileReader->read($path);
        $results = $reader->fetchAssoc();
        return $this->csvFileValidator->validate($results);
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

    /**
     * @param String $filePath
     */
    public function removeFile(String $filePath): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($filePath);
    }
}

