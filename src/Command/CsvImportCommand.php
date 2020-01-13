<?php
namespace App\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\CSVImportWorker;

class CsvImportCommand extends Command
{

    protected static $defaultName = 'csv:import';

    /**
    * @var CSVImportWorker
    */
    private $csvImportWorker;


    public function __construct(CSVImportWorker $csvImportWorker)
     {
         parent::__construct();
         $this->csvImportWorker = $csvImportWorker;
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
        $this->csvImportWorker->importProducts($input, $output);
        return 0;
    }
}
