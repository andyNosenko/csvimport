<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class CSVImportWorker
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CsvFileReader
     */
    private $csvReadFile;

    /**
     * @var CSVFileValidation
     */
    private $csvFileValidation;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $processed;

    /**
     * @var int
     */
    private $skipped;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var String
     */
    private $senderEmail;

    /**
     * @var String
     */
    private $recipientEmail;

    /**
     * @return int
     */
    public function getSkipped(): int
    {
        return $this->skipped;
    }

    /**
     * @param int $skipped
     */
    public function setSkipped(int $skipped)
    {
        $this->skipped = $skipped;
    }

    /**
     * @return int
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param int $processed
     */
    public function setProcessed(int $processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $total
     */
    public function setTotal(int $total)
    {
        $this->total = $total;
    }

    /**
     * CSVImportWorker constructor.
     * @param EntityManagerInterface $em
     * @param \App\Service\CsvFileReader $csvReadFile
     * @param \App\Service\CSVFileValidation $csvFileValidation
     * @param MailerInterface $mailer
     * @param String $senderEmail
     * @param String $recipientEmail
     */
    public function __construct(
        EntityManagerInterface $em,
        CsvFileReader $csvReadFile,
        CSVFileValidation $csvFileValidation,
        MailerInterface $mailer,
        String $senderEmail,
        String $recipientEmail
    ) {
        $this->em = $em;
        $this->csvReadFile = $csvReadFile;
        $this->csvFileValidation = $csvFileValidation;
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * @param String $path
     * @param bool $isTest
     */
    public function importProducts(String $path, Bool $isTest)
    {
        $this->csvReadFile->read($path);
        $validate = $this->csvFileValidation->validate();
        $results = $validate->fetchAssoc();
        $this->setTotal(iterator_count($results));

        if (!$this->stopImportProducts()) {
            $this->importToDatabase($results, $isTest);
        }
    }

    /**
     * @param \Iterator $results
     * @param bool $isTest
     */
    public function importToDatabase(\Iterator $results, Bool $isTest): void
    {
        foreach ($results as $row) {
            if ($this->importProductRequirements($row)) {
                    $product = (new Product())
                        ->setProductCode($row['Product Code'])
                        ->setProductName($row['Product Name'])
                        ->setProductDescription($row['Product Description'])
                        ->setStock((int)$row['Stock'])
                        ->setCost((int)$row['Cost in GBP'])
                        ->setDiscontinued($row['Discontinued']);

                    $this->em->persist($product);
                    if (!$isTest) {
                        $this->em->flush();
                    }

                    $this->processed++;
                }
            }

        $this->setSkipped($this->getTotal() - $this->getProcessed());

    }

    /**
     * @return String
     */
    public function stopImportProducts()
    {
        return $this->csvFileValidation->getErrorMessage();
    }

    /**
     * @param $row
     * @return bool
     */
    public function importProductRequirements($row): bool
    {
        if ($row['Cost in GBP'] > 5 && $row['Stock'] > 10) {
            if ($row['Cost in GBP'] <= 1000) {
                return true;
            }
        }
        return false;
    }


    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail():void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($this->recipientEmail)
            ->subject("Products import report")
            ->htmlTemplate("csv/report.html.twig")
            ->context([
                'total' => $this->getTotal(),
                'skipped' => $this->getSkipped(),
                'processed' => $this->getProcessed(),
            ]);

        $this->mailer->send($email);
    }
}
