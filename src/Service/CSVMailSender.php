<?php

declare(strict_types=1);

namespace App\Service;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class CSVMailSender
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var CSVImportWorker
     */
    private $csvImportWorker;

    /**
     * @var String
     */
    private $senderEmail;

    /**
     * @var String
     */
    private $recipientEmail;

    /**
     * @param String $senderEmail
     * @param String $recipientEmail
     * @param MailerInterface $mailer
     * @param CSVImportWorker $csvImportWorker
     */
    public function __construct(
        String $senderEmail,
        String $recipientEmail,
        MailerInterface $mailer,
        CSVImportWorker $csvImportWorker
    ) {
        $this->senderEmail = $senderEmail;
        $this->recipientEmail = $recipientEmail;
        $this->mailer = $mailer;
        $this->csvImportWorker = $csvImportWorker;
    }

    /**
     * @param CSVImportWorker $csvImportWorker
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail(CSVImportWorker $csvImportWorker):void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($this->recipientEmail)
            ->subject("Products import report")
            ->htmlTemplate("csv/report.html.twig")
            ->context([
                'total' => $csvImportWorker->total,
                'skipped' => $csvImportWorker->skipped,
                'processed' => $csvImportWorker->processed,
            ]);

        $this->mailer->send($email);
    }
}

