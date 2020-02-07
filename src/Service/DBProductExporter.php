<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class DBProductExporter
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var PaginatorInterface
     */
    protected  $paginator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     */
    public function __construct(EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $this->em = $entityManager;
        $this->paginator = $paginator;
    }

    /**
     * @param $request
     * @return PaginationInterface
     */
    public function ReturnProducts($request)
    {
       return $this->paginator->paginate(
            $this->em->getRepository(Product::class)->findAll(),
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }
}
