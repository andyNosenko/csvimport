<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use App\Service\CSVNotifier;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ProductConsumer implements ConsumerInterface
{
    /**
     * @var CSVImportWorker
     */
    private $csvImportWorker;

    /**
     * @var CSVMailSender
     */
    private $csvMailSender;

    /**
     * @var CSVNotifier
     */
    private $csvNotifier;

    /**
     * @var int
     */
    private $isValid;

    /**
     * @var int
     */
    private $isReported;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param CSVImportWorker $csvImportWorker
     * @param CSVMailSender $csvMailSender
     * @param CSVNotifier $csvNotifier
     * @param Filesystem $filesystem
     */
    public function __construct(
        CSVImportWorker $csvImportWorker,
        CSVMailSender $csvMailSender,
        CSVNotifier $csvNotifier,
        Filesystem $filesystem
    ) {
        $this->csvImportWorker = $csvImportWorker;
        $this->csvMailSender = $csvMailSender;
        $this->csvNotifier = $csvNotifier;
        $this->isValid = true;
        $this->isReported = 0;
        $this->filesystem = $filesystem;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $body = json_decode($msg->body, true);

        if (file_exists($body['path_file'])) {
            $this->csvImportWorker->importProducts($body['path_file'], false);
//            $this->sendEmail();

            $this->csvImportWorker->getErrors() ? $this->isValid = 0 : $this->isValid = 1;

            $this->createNotification($body);
            $this->csvImportWorker->resetProductCounters();
            $this->removeFile($body['path_file']);
            echo 'Обработан успешно: ' . $body['path_file'] . PHP_EOL;
        } else {
            echo 'Файл с таким именем уже был обработан: ' . $body['path_file'] . PHP_EOL;
        }
    }

    private function sendEmail()
    {
        try {
            $this->csvMailSender->sendEmail(
                [
                    'total' => $this->csvImportWorker->getTotalCount(),
                    'skipped' => $this->csvImportWorker->getSkippedCount(),
                    'processed' => $this->csvImportWorker->getProcessedCount(),
                    'products' => $this->csvImportWorker->getProducts(),
                    'errors' => $this->csvImportWorker->getErrors(),
                ],
                false
            );
        } catch (TransportExceptionInterface $e) {
        }
        //$this->isReported = 1;
    }

    /**
     * @param $body
     */
    private function createNotification($body)
    {
        $this->csvNotifier->createNotification(
            $body['path_file'],
            \DateTime::createFromFormat('Y-m-d H:i:s', $body['dateTimeUploaded']),
            (bool) $this->isValid,
            (bool) $this->isReported,
            $body['user']
        );
    }

    /**
     * @param String $filePath
     */
    public function removeFile(String $filePath): void
    {
        $this->filesystem->remove($filePath);
    }
}