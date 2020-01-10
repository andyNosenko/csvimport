<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(): Response
    {
       $em = $this->getDoctrine()->getManager();

       $product = (new Product())
         ->setProductCode("P0002")
         ->setProductName('TV')
         ->setProductDescription('32 Tv')
         ->setStock(10)
         ->setCost(399.99)
         ->setDiscontinued('');

       $em->persist($product);
       $em->flush();


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProudctController.php',
        ]);
    }
}
