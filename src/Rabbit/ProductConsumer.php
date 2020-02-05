<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use App\Service\CSVNotifier;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
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
     * @param CSVImportWorker $csvImportWorker
     * @param CSVMailSender $csvMailSender
     * @param CSVNotifier $csvNotifier
     */
    public function __construct(
        CSVImportWorker $csvImportWorker,
        CSVMailSender $csvMailSender,
        CSVNotifier $csvNotifier
    ) {
        $this->csvImportWorker = $csvImportWorker;
        $this->csvMailSender = $csvMailSender;
        $this->csvNotifier = $csvNotifier;
        $this->isValid = 1;
        $this->isReported = 0;
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
            $this->sendEmail();

            $this->csvImportWorker->getErrors() ? $this->isValid = 0 : $this->isValid = 1;

            $this->addNotification($body);
            $this->resetProductCounters();
            $this->csvImportWorker->removeFile($body['path_file']);
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
                    'total' => $this->csvImportWorker->totalCount,
                    'skipped' => $this->csvImportWorker->skippedCount,
                    'processed' => $this->csvImportWorker->processedCount,
                    'products' => $this->csvImportWorker->products,
                    'errors' => $this->csvImportWorker->getErrors(),
                ],
                false
            );
        } catch (TransportExceptionInterface $e) {
        }
        $this->isReported = 1;
    }

    /**
     * @param $body
     */
    private function addNotification($body)
    {
        $this->csvNotifier->addNotification(
            $body['path_file'],
            \DateTime::createFromFormat('Y-m-d H:i:s', $body['dateTime']),
            (bool) $this->isValid,
            (bool) $this->isReported
        );
    }

    private function resetProductCounters()
    {
        $this->csvImportWorker->products = new \ArrayObject();
        $this->csvImportWorker->totalCount = 0;
        $this->csvImportWorker->skippedCount = 0;
        $this->csvImportWorker->processedCount = 0;
        $this->csvImportWorker->resetErrors();
    }
}