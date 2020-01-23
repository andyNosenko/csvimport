<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DBProductExporter
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager,
                                ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    /**
     * @param $request
     * @return PaginationInterface
     */
    public function ReturnProducts($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
                SELECT
                        t.id,
                        t.productCode,
                        t.productName,
                        t.productDescription,
                        t.stock,
                        t.cost,
                        t.discontinued
                FROM
                    App\Entity\Product t
            '
        );
        $paginator = $container->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
        return ($result);
    }
}
