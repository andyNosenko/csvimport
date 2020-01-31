<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use App\Service\CSVNotifier;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

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

    private $csvNotifier;

    private $isValid;

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
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function execute(AMQPMessage $msg)
    {
        //$msg->getBody();
        $body = json_decode($msg->body, true);
        if (file_exists($body['path_file'])) {
            $this->csvImportWorker->importProducts($body['path_file'], false);
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
            $this->isReported = 1;

            if ($this->csvImportWorker->getErrors()) {
                $this->isValid = 0;
            }
            $this->csvNotifier->addNotification(
                $body['path_file'],
                \DateTime::createFromFormat('Y-m-d H:i:s', $body['dateTime']),
                (bool) $this->isValid,
                (bool) $this->isReported
            );

            $this->csvImportWorker->products = new \ArrayObject();
            $this->csvImportWorker->totalCount = 0;
            $this->csvImportWorker->skippedCount = 0;
            $this->csvImportWorker->processedCount = 0;
            $this->csvImportWorker->resetErrors();
            $this->isValid = 1;
            $this->csvImportWorker->removeFile($body['path_file']);
            echo 'Обработан успешно: ' . $body['path_file'] . PHP_EOL;
        } else {
            echo 'Файл с таким именем уже был обработан: ' . $body['path_file'] . PHP_EOL;
        }
    }
}