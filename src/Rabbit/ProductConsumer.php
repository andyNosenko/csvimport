<?php


namespace App\Rabbit;

use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ProductConsumer implements ConsumerInterface
{
    /**
     * @var CSVImportWorker
     */
    private $csvImportWorker;

    private $csvMailSender;

    /**
     * @param CSVImportWorker $csvImportWorker
     * @param CSVMailSender $csvMailSender
     */
    public function __construct(CSVImportWorker $csvImportWorker, CSVMailSender $csvMailSender)
    {
        $this->csvImportWorker = $csvImportWorker;
        $this->csvMailSender = $csvMailSender;
    }

    /**
     * @param AMQPMessage $msg
     * @return mixed|void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function execute(AMQPMessage $msg)
    {
        if (file_exists($msg->getBody())) {
            $this->csvImportWorker->importProducts($msg->getBody(), false);
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
            $this->csvImportWorker->products = new \ArrayObject();
            echo 'Обработан успешно: '.$msg->getBody().PHP_EOL;
        } else {
            echo 'Файл с таким именем уже был обработан: '.$msg->getBody().PHP_EOL;
        }
    }
}