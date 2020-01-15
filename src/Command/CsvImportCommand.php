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
            ->addArgument('file_name', InputArgument::OPTIONAL, 'Choose your file with .csv extension...', "stock.csv")
            ->addOption('test', null, InputOption::VALUE_NONE, 'Test execution without database insertion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Products...');
        $workFolder = '/home/ITRANSITION.CORP/a.nosenko/Documents/Projects/CSVimport/src/Data/';

        $file = $input->getArgument("file_name");

        if($input->getOption("test")) {
            $this->csvImportWorker->importProducts($workFolder.$file, true);
        } else {
            $this->csvImportWorker->importProducts($workFolder.$file, false);
            $this->csvImportWorker->sendEmail();
        }


        $io->note("Found products: " . $this->csvImportWorker->getTotal());
        $io->warning($this->csvImportWorker->getSkipped() . ' items were skiped');
        $io->success($this->csvImportWorker->getProcessed() . ' items imported seccessfuly!');

        return 0;
    }
}
