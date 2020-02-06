<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CSVFileType;
use App\Service\CSVImportWorker;
use App\Service\CSVNotifier;
use App\Service\DBProductExporter;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @Route("/importfile", name="importfile")
     * @param Request $request
     * @param CSVImportWorker $csvImportWorker
     * @param DBProductExporter $dbProductExporter
     * @return Response
     */
    public function csvImportAction(
        Request $request,
        CSVImportWorker $csvImportWorker,
        DBProductExporter $dbProductExporter
    ) {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);
        $filePath = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $isTest = $form["test"]->getData();

            $destination = $this->getParameter('uploadDirectory');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filePath = $destination . $originalFilename . ".csv";
            $file->move($destination, $originalFilename . ".csv");

            //$container->get('old_sound_rabbit_mq.parsing_producer')->add($destination . $originalFilename . ".csv");
            $this->producer->add($filePath);
            //$csvImportWorker->importProducts($destination . $originalFilename . ".csv", $isTest);
        }

        $productsView = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
            'productsView' => $productsView,
            'productPath' => $filePath,
        ]);
    }


    /**
     * @Route("/notification/{filePath}", name="notification", requirements={"filePath"=".+"})
     * @param Request $request
     * @param CSVNotifier $csvNotifier
     * @param String $filePath
     * @return JsonResponse|Response
     */
    public function notifyAction(Request $request, CSVNotifier $csvNotifier, String $filePath)
    {
        $log = $csvNotifier->getNotificationByFilePath($filePath);
        $validationInfo = $log->getIsValid() ? "is valid" : "is invalid";
        $reportInfo = $log->getIsReported() ? "Report has been sent to your email:)" : "Report hasn't been sent to your email:(";
        $notification = sprintf("Your file %s %s. %s",
            $log->getFileName(),
            $validationInfo,
            $reportInfo
        );
        $arrData = [
            'notification' => $notification
        ];

        return new JsonResponse($arrData);
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

