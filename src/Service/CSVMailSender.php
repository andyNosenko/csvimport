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
     */
    public function __construct(
        String $senderEmail,
        String $recipientEmail,
        MailerInterface $mailer
    )
    {
        $this->senderEmail = $senderEmail;
        $this->recipientEmail = $recipientEmail;
        $this->mailer = $mailer;
    }

    /**
     * @param array $attributes
     * @param bool $isTest
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail(Array $attributes, bool $isTest): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($this->recipientEmail)
            ->subject("Products import report")
            ->htmlTemplate("csv/report.html.twig")
            ->context($attributes);
        if (!$isTest) {
            $this->mailer->send($email);
        }
    }
}

