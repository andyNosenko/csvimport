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
            ->addArgument('file_name', InputArgument::OPTIONAL, 'Choose your file with .csv extension...')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Products...');
        $workFolder = '/home/ITRANSITION.CORP/a.nosenko/Documents/Projects/CSVimport/src/Data/';

        $input->getArgument("file_name") ? $file = $input->getArgument("file_name") : $file = "stock.csv";
        $this->csvImportWorker->importProducts($workFolder.$file);

        $io->note("Found products: " . $this->csvImportWorker->getTotal());
        $io->warning($this->csvImportWorker->getSkipped() . ' items were skiped');
        $io->success($this->csvImportWorker->getProcessed() . ' items imported seccessfuly!');

        return 0;
    }
}
