<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\CSVImportWorker;

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
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    /**
     * @Route("/csv", name="csv")
     */
     public function csv(CSVImportWorker $csvReaderWorker):Response
     {
       $message = $csvReaderWorker->getHappyMessage();


       return new Response('<h1>'.$message.'</h1>');
     }
}
