<?php

declare(strict_types=1);

namespace App\Service;


use App\Entity\ProductLog;
use Doctrine\ORM\EntityManagerInterface;

class CSVNotifier
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct( EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param String $filePath
     * @param \DateTime $dateTime
     * @param bool $isValid
     * @param bool $isReported
     */
    public function addNotification(
        String $filePath,
        \DateTime $dateTime,
        Bool $isValid,
        Bool $isReported
    ) {
        $productLog = new ProductLog();
        $productLog->setFileName($filePath);
        $productLog->setDateTime($dateTime);
        $productLog->setIsValid($isValid);
        $productLog->setIsReported($isReported);
        $this->em->persist($productLog);
        $this->em->flush();

    }

    /**
     * @param String $filePath
     * @return ProductLog|object|null
     */
    public function getNotificationByFilePath(String $filePath)
    {
        return $this->em->getRepository(ProductLog::class)->findOneBy([
            'fileName' => $filePath,
        ]);
    }
}