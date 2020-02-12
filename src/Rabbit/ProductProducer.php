<?php

declare(strict_types=1);

namespace App\Rabbit;

use App\Entity\User;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ProductProducer extends Producer
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param $pathFile
     * @param User $user
     */
    public function add($pathFile, int $user_id) {
        $message = [
            'path_file' => $pathFile,
            'dateTimeUploaded' => date('Y-m-d H:i:s'),
            'user' => $user_id
        ];
        $this->producer->publish(json_encode($message));
    }

}