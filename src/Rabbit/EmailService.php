<?php


namespace App\Rabbit;

use App\Service\CSVImportWorker;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class EmailService implements ConsumerInterface
{
    private $csvImportWorker;

    public function __construct(CSVImportWorker $csvImportWorker)
    {
        $this->csvImportWorker = $csvImportWorker;
    }

    public function execute(AMQPMessage $msg)
    {
        echo 'Ну тут типа сообщение пытаюсь отправить: '.$msg->getBody().PHP_EOL;
        echo 'Отправлено успешно!...';
        $this->csvImportWorker->importProducts($msg->getBody(), false);
    }
}