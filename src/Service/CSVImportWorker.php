<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use App\Service\CSVReadFile;
use App\Service\CSVFileValidation;


class CSVImportWorker
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CSVReadFile
     */
    private $csvReadFile;

    /**
     * @var CSVFileValidation
     */
    private $csvFileValidation;

    private $total;

    private $processed;

    private $skipped;

    /**
     * @return mixed
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * @param mixed $skipped
     */
    public function setSkipped($skipped): void
    {
        $this->skipped = $skipped;
    }

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed): void
    {
        $this->processed = $processed;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function __construct(EntityManagerInterface $em, CSVReadFile $csvReadFile, CSVFileValidation $csvFileValidation)
    {
        $this->em = $em;
        $this->csvReadFile = $csvReadFile;
        $this->csvFileValidation = $csvFileValidation;
    }

   public function importProducts(InputInterface $input, OutputInterface $output)
   {
       $io = new SymfonyStyle($input, $output);
       $io->title('Attempting import of Products...');

       $this->csvReadFile->read('%kernel.root_dir%/../src/Data/stock.csv');
       //$validate = $this->csvReadFile->validate($reader);
       $validate = $this->csvFileValidation->validate();
       $errorMessage = $this->csvFileValidation->getErrorMessage();
       $results = $validate->fetchAssoc();
       $total = iterator_count($results);
       $io->note("Found products: " . $total);
       $processed = 0;

       if (!$errorMessage) {

         $table = new Table($output);
         $table->setHeaders(['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued']);

           foreach ($results as $row) {
               if ($row['Cost in GBP'] > 5 && $row['Stock'] > 10) {
                   if ($row['Cost in GBP'] <= 1000) {
                       $product = (new Product())
                           ->setProductCode($row['Product Code'])
                           ->setProductName($row['Product Name'])
                           ->setProductDescription($row['Product Description'])
                           ->setStock((int)$row['Stock'])
                           ->setCost((int)$row['Cost in GBP'])
                           ->setDiscontinued($row['Discontinued']);

                       $this->em->persist($product);
                       $processed++;
                   }
               } else {
                  $table->setRows([
                      [
                        $row['Product Code'],
                        $row['Product Name'],
                        $row['Product Description'],
                        $row['Stock'],
                        $row['Cost in GBP'],
                        $row['Discontinued']
                      ]
                  ]);
                  $table->render();
               }
           }
       } else {
           $io->warning($errorMessage);
       }

       $this->em->flush();

       $io->warning($total - $processed . ' items were skiped');
       $io->success($processed . ' items imported seccessfuly!');
   }

    // public function importProducts($path)
    // {
    //     //$path = 'C:\Projects\Itransition\OpenServer\OSPanel\domains\csvimport\src\Data\stock.csv';
    //     //$path = '../src/Data/stock.csv';
    //     $this->csvReadFile->read($path);
    //     //$validate = $this->csvReadFile->validate($reader);
    //     $validate = $this->csvFileValidation->validate();
    //     $errorMessage = $this->csvFileValidation->getErrorMessage();
    //     $results = $validate->fetchAssoc();
    //     $this->setTotal(iterator_count($results));
    //     $processed = 0;
    //
    //     if (!$errorMessage) {
    //         foreach ($results as $row) {
    //             if ($row['Cost in GBP'] > 5 && $row['Stock'] > 10) {
    //                 if ($row['Cost in GBP'] <= 1000) {
    //                     $product = (new Product())
    //                         ->setProductCode($row['Product Code'])
    //                         ->setProductName($row['Product Name'])
    //                         ->setProductDescription($row['Product Description'])
    //                         ->setStock((int)$row['Stock'])
    //                         ->setCost((int)$row['Cost in GBP'])
    //                         ->setDiscontinued($row['Discontinued']);
    //
    //                     $this->em->persist($product);
    //                     $processed++;
    //                 }
    //             }
    //         }
    //     } else {
    //
    //     }
    //     $this->setProcessed($processed);
    //     $this->setSkipped($this->getTotal() - $this->getProcessed());
    //     $this->em->flush();
    //
    //
    // }
}
