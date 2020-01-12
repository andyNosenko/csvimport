<?php

namespace App\Controller;


use App\Form\CSVFileType;
use App\Service\CSVFileValidation;
use App\Service\CSVReadFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
//       $em = $this->getDoctrine()->getManager();
//
//       $product = (new Product())
//         ->setProductCode("P0002")
//         ->setProductName('TV')
//         ->setProductDescription('32 Tv')
//         ->setStock(10)
//         ->setCost(399.99)
//         ->setDiscontinued('');
//
//       $em->persist($product);
//       $em->flush();


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    /**
 * @Route("/csv", name="csv")
 * @param EntityManagerInterface $em
 * @param CSVReadFile $csvReadFile
 * @param CSVFileValidation $csvFileValidation
 * @return Response
 */
    public function csv(EntityManagerInterface $em,
                        CSVReadFile $csvReadFile,
                        CSVFileValidation $csvFileValidation): Response
    {
        $csvImportWorker = new CSVImportWorker($em, $csvReadFile, $csvFileValidation);
        $csvImportWorker->importProducts();

        return new Response('<h1>Total items found: '.$csvImportWorker->getTotal().
            '<br>Items were skipped: '.$csvImportWorker->getSkipped().
            '<br>Items were processed: '.$csvImportWorker->getProcessed().'</h1>');
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(Request $request)
    {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $extension = $file->guessExtension();
            if ($extension  == 'csv' || $extension == 'txt') {
                $destination = $this->getParameter('kernel.project_dir').'/src/Data';

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                if ($extension == "txt") {
                    $fileName = $originalFilename.'.'.'csv';
                } else {
                    $fileName = $originalFilename.'.'.$file->guessExtension();
                }
                $file->move($destination, $fileName);
                return new Response('<h1>Submitted!</h1>');
            } else {
                return new Response('<h1>Only csv files allowed!</h1>');
            }
        }
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
