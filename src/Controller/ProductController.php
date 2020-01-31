<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ProductLog;
use App\Form\CSVFileType;
use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use App\Service\CSVNotifier;
use App\Service\DBProductExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    /**
     * @Route("/importfile", name="importfile")
     * @param Request $request
     * @param CSVImportWorker $csvImportWorker
     * @param ContainerInterface $container
     * @param DBProductExporter $dbProductExporter
     * @param CSVNotifier $csvNotifier
     * @return Response
     */
    public function csvImportAction(
        Request $request,
        CSVImportWorker $csvImportWorker,
        ContainerInterface $container,
        DBProductExporter $dbProductExporter,
        CSVNotifier $csvNotifier
    )
    {
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
            $log = $csvNotifier->getNotificationByFilePath($destination . $originalFilename . ".csv");
            if ($log) {
                $validationInfo = $log->getIsValid() ? "is valid" : "is invalid";
                $reportInfo = $log->getIsReported() ? "Report has been sent to your email:)" : "Report hasn't been sent to your email:(";
                $this->addFlash(
                    'notice',
                    sprintf("Your file %s %s. %s",
                        $log->getFileName(),
                        $validationInfo,
                        $reportInfo
                    )
                );
            }
        }
        $productsView = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
            'productsView' => $productsView,
        ]);
    }

    /**
     * @Route("/test", name="test")
     * @param CSVNotifier $csvNotifier
     * @return JsonResponse
     */
    public function test(CSVNotifier $csvNotifier)
    {
        $log = $csvNotifier->getNotificationByFilePath("/home/ITRANSITION.CORP/a.nosenko/Documents/Projects/CSVimport/src/Data/uploaded/stock.csv");
        return new JsonResponse([
            "file_path" => $log->getFileName(),
            "date_time" => $log->getDateTime(),
            "is_valid" => $log->getIsValid(),
            "is_reported" => $log->getIsReported()
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

