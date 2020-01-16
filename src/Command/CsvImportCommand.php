<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CSVImportWorker;
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
     * CsvImportCommand constructor.
     * @param CSVImportWorker $csvImportWorker
     * @param String $targetDirectory
     */
    public function __construct(CSVImportWorker $csvImportWorker, String $targetDirectory)
    {
        parent::__construct();

        $this->csvImportWorker = $csvImportWorker;
        $this->targetDirectory = $targetDirectory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import CSV file into database')
            ->addArgument('file_name', InputArgument::OPTIONAL, 'Choose your file with .csv extension...', "stock.csv")
            ->addOption('test', null, InputOption::VALUE_NONE, 'Test execution without database insertion');
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

        if ($input->getOption("test")) {
            $this->csvImportWorker->importProducts($this->targetDirectory . $file, true);
        } else {
            $this->csvImportWorker->importProducts($this->targetDirectory . $file, false);
            $this->csvImportWorker->sendEmail();
        }


        $io->note("Found products: " . $this->csvImportWorker->getTotal());
        $io->warning($this->csvImportWorker->getSkipped() . ' items were skiped');
        $io->success($this->csvImportWorker->getProcessed() . ' items imported seccessfuly!');

        return 0;
    }
}
