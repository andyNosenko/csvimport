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

      $reader = $this->csvReadFile->read('%kernel.root_dir%/../src/Data/stock.csv');
      //$validate = $this->csvReadFile->validate($reader);
      $validate = $this->csvFileValidation->validate();
      $errorMessage = $this->csvFileValidation->getErrorMessage();
      $results = $validate->fetchAssoc();
      $total = iterator_count($results);
      $io->note("Found products: " .$total);
      $processed = 0;

      if (!$errorMessage) {
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
          }
        }
      }
      else {
        $io->warning($errorMessage);
      }
      
      $this->em->flush();

      $io->warning($total - $processed.' items were skiped');
      $io->success($processed.' items imported seccessfuly!');
    }
}
