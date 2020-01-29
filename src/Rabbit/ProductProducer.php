<?php


namespace App\Rabbit;


use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class ProductProducer
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @param ProducerInterface $producer
     */
    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param String $pathFile
     */
    public function add(String $pathFile) {
        $this->producer->publish($pathFile);
    }

}