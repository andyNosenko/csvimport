<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $product = (new Product())
          ->setProductCode("P001")
          ->setProductName('TV')
          ->setProductDescription('32" Tv')
          ->setStock(10)
          ->setCost(399.99)
          ->setDiscontinued('');

        $manager->persist($product);
        $manager->flush();
    }
}
