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
     */
    public function __construct(EntityManagerInterface $em, CsvFileReader $csvReadFile,
                                CSVFileValidation $csvFileValidation, MailerInterface $mailer
    )
    {
        $this->em = $em;
        $this->csvReadFile = $csvReadFile;
        $this->csvFileValidation = $csvFileValidation;
        $this->mailer = $mailer;
    }

    public function importProducts($path, $isTest)
    {
        //$path = 'C:\Projects\Itransition\OpenServer\OSPanel\domains\csvimport\src\Data\stock.csv';
        //$path = '../src/Data/stock.csv';
        $this->csvReadFile->read($path);
        $validate = $this->csvFileValidation->validate();
        $results = $validate->fetchAssoc();
        $this->setTotal(iterator_count($results));

        if (!$this->stopImportProducts()) {
            $this->importToDatabase($results, $isTest);
        }
    }

    public function importToDatabase($results, $isTest)
    {
        $processed = 0;
        foreach ($results as $row) {
            if ($row['Cost in GBP'] > 5 && $row['Stock'] > 10) {
                if ($row['Cost in GBP'] <= 1000) {
                    if (!$isTest) {
                        $product = (new Product())
                            ->setProductCode($row['Product Code'])
                            ->setProductName($row['Product Name'])
                            ->setProductDescription($row['Product Description'])
                            ->setStock((int)$row['Stock'])
                            ->setCost((int)$row['Cost in GBP'])
                            ->setDiscontinued($row['Discontinued']);

                        $this->em->persist($product);
                        $this->em->flush();
                    }

                    $processed++;
                }
            }
        }

        $this->setProcessed($processed);
        $this->setSkipped($this->getTotal() - $this->getProcessed());

    }

    public function stopImportProducts()
    {
        return $this->csvFileValidation->getErrorMessage();
    }


    public function sendEmail()
    {
        $email = (new TemplatedEmail())
            ->from("admin@test.com")
            ->to("eaff1fac09-eefa97@inbox.mailtrap.io")
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
