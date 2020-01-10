<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Csv\Reader;

class CsvImportCommand extends Command
{

  /**
    * @var EntityManagerInterface
    */
   private $em;

    protected static $defaultName = 'csv:import';

    /**
      * CsvImportCommand constructor.
      *
      * @param EntityManagerInterface $em
      *
      * @throws \Symfony\Component\Console\Exception\LogicException
      */
     public function __construct(EntityManagerInterface $em)
     {
         parent::__construct();

         $this->em = $em;
     }

    protected function configure()
    {
        $this
            ->setDescription('Import CSV file into database')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Products...');

        $reader = Reader::createFromPath('%kernel.root_dir%/../src/Data/stock.csv');

        $results = $reader->fetchAssoc();
        $total = iterator_count($results);
        $io->note("Found products: " .$total);
        $processed = 0;
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

        $this->em->flush();
        $io->progress()
        $io->warning($total - $processed.' items were skiped');
        $io->success($processed.' items imported seccessfuly!');

        return 0;
    }
}
