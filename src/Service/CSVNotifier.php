<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ProductUploadLog;
use App\Entity\User;
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
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param String $filePath
     * @param \DateTime $dateTime
     * @param bool $isValid
     * @param bool $isReported
     * @param int $userId
     */
    public function createNotification(
        String $filePath,
        \DateTime $dateTime,
        Bool $isValid,
        Bool $isReported,
        Int $userId
    ) {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'id' => $userId
        ]);

        $productLog = new ProductUploadLog();
        $productLog->setFileName($filePath);
        $productLog->setProcessedDateTime($dateTime);
        $productLog->setIsValid($isValid);
        $productLog->setIsReported($isReported);
        $productLog->setUser($user);
        $this->em->persist($productLog);
        $this->em->flush();
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getAllUserNotifications(int $userId)
    {
       return $this->em->getRepository(ProductUploadLog::class)->findBy([
           'isReported' => 0,
           'user' => $userId,
       ]);
    }

    public function setAsReportedUserNotifications(Array $notifications)
    {
        foreach ($notifications as $log) {
            $log->setIsReported(true);
            $this->em->persist($log);
            $this->em->flush();
        }
    }
}