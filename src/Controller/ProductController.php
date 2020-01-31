<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CSVFileType;
use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use App\Service\DBProductExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    /**
     * @Route("/importfile", name="importfile")
     * @param Request $request
     * @param CSVImportWorker $csvImportWorker
     * @param CSVMailSender $csvMailSender
     * @param ContainerInterface $container
     * @param DBProductExporter $dbProductExporter
     * @return Response
     */
    public function csvImportAction(
        Request $request,
        CSVImportWorker $csvImportWorker,
        ContainerInterface $container,
        DBProductExporter $dbProductExporter
    ) {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $isTest = $form["test"]->getData();

            $destination = $this->getParameter('uploadDirectory');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $file->move($destination, $originalFilename . ".csv");
            $container->get('old_sound_rabbit_mq.parsing_producer')->add($destination . $originalFilename . ".csv");
            //$csvImportWorker->importProducts($destination . $originalFilename . ".csv", $isTest);

            $this->addFlash(
                'notice',
                'Your file was successfully uploaded!'
            );

            if ($csvImportWorker->getErrors()) {
                $container->get('old_sound_rabbit_mq.parsing_producer')->add($destination . $originalFilename . ".csv");
                //$csvImportWorker->removeFile($destination . $originalFilename . ".csv");
                return $this->render("csv/errors.html.twig", [
                    'errors' => $csvImportWorker->getErrors(),
                ]);
            }

            return $this->render("csv/report.html.twig", [
                'total' => $csvImportWorker->totalCount,
                'skipped' => $csvImportWorker->skippedCount,
                'processed' => $csvImportWorker->processedCount,
                'products' => $csvImportWorker->products,
                'errors' => $csvImportWorker->getErrors(),
            ]);
        }
        $productsView = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
            'productsView' => $productsView
        ]);
    }

    /**
     * @Route("/view", name="ProductsView")
     * @param Request $request
     * @param DBProductExporter $dbProductExporter
     * @return Response
     */
    public function view(Request $request, DBProductExporter $dbProductExporter)
    {
        $products = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/view.html.twig', [
            'products' => $products,
        ]);
    }
}

