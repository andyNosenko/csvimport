<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CsvImportCommand extends Command
{
    /**
     * @var String
     */
    private $targetDirectory;

    /**
     * @var string
     */
    protected static $defaultName = 'csv:import';

    /**
     * @var CSVImportWorker
     */
    private $csvImportWorker;

    /**
     * @var CSVMailSender
     */
    private $csvMailSender;

    /**
     * @param CSVImportWorker $csvImportWorker
     * @param CSVMailSender $csvMailSender
     * @param String $targetDirectory
     */
    public function __construct(CSVImportWorker $csvImportWorker, CSVMailSender $csvMailSender, String $targetDirectory)
    {
        parent::__construct();

        $this->csvImportWorker = $csvImportWorker;
        $this->csvMailSender = $csvMailSender;
        $this->targetDirectory = $targetDirectory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import CSV file into database')
            ->addArgument('file_name', InputArgument::OPTIONAL, 'Choose your file with .csv extension...', "stock.csv")
            ->addOption('test', null, InputOption::VALUE_OPTIONAL, 'Test execution without database insertion', true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Products...');

        $file = $input->getArgument("file_name");

        $test = $input->getOption("test");
        $this->csvImportWorker->importProducts($this->targetDirectory . $file, (bool)$test == false);
        //$this->csvMailSender->sendEmail($this->csvImportWorker, (bool)$test==false);
        $this->csvMailSender->sendEmail([
            'total' => $this->csvImportWorker->total,
            'skipped' => $this->csvImportWorker->skipped,
            'processed' => $this->csvImportWorker->processed,
            'products' => $this->csvImportWorker->products
        ], (bool)$test == false);


        $io->note("Found products: " . $this->csvImportWorker->total);
        $io->warning($this->csvImportWorker->skipped . ' items were skiped');
        $io->success($this->csvImportWorker->processed . ' items imported seccessfuly!');

        return 0;
    }
}

