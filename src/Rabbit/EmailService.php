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
        if (file_exists($msg->getBody())) {
            $this->csvImportWorker->importProducts($msg->getBody(), false);
            echo 'Обработан успешно!...';
        } else {
            echo 'Файл с таким именем уже был обработан: '.$msg->getBody().PHP_EOL;
        }
    }
}