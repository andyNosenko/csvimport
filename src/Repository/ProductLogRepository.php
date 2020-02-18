<?php

namespace App\Repository;

use App\Entity\ProductUploadLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ProductUploadLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductUploadLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductUploadLog[]    findAll()
 * @method ProductUploadLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductUploadLog::class);
    }
}
