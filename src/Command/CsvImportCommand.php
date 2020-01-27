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
    public function __construct(
        CSVImportWorker $csvImportWorker,
        CSVMailSender $csvMailSender,
        String $targetDirectory
    ) {
        parent::__construct();

        $this->csvImportWorker = $csvImportWorker;
        $this->csvMailSender = $csvMailSender;
        $this->targetDirectory = $targetDirectory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import CSV file into database')
            ->addArgument('file_name', InputArgument::OPTIONAL,
                'Choose your file with .csv extension...', "stock1.csv")
            ->addOption('test', null,
                InputOption::VALUE_OPTIONAL, 'Test execution without database insertion', true);
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
        $this->csvImportWorker->importProducts($this->targetDirectory . $file, (bool) $test);
        $this->csvMailSender->sendEmail(
            [
                'total' => $this->csvImportWorker->totalCount,
                'skipped' => $this->csvImportWorker->skippedCount,
                'processed' => $this->csvImportWorker->processedCount,
                'products' => $this->csvImportWorker->products,

            ],
            (bool) !$test
        );

        $io->note("Found products: " . $this->csvImportWorker->totalCount);
        $io->warning($this->csvImportWorker->skippedCount . ' items were skipped');
        $io->success($this->csvImportWorker->processedCount . ' items imported successfully!');

        return 0;
    }
}

