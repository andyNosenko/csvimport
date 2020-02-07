<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CSVFileType;
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
     * @param DBProductExporter $dbProductExporter
     * @return Response
     */
    public function csvImportAction(
        Request $request,
        DBProductExporter $dbProductExporter
    ) {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $isTest = $form["test"]->getData();

            $destination = $this->getParameter('uploadDirectory');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filePath = $destination . $originalFilename . ".csv";
            $file->move($destination, $originalFilename . ".csv");

            $this->producer->add($filePath);
        }

        $productsView = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
            'productsView' => $productsView,
        ]);
    }

    /**
     * @Route("/notifications", name="notifications")
     * @param Request $request
     * @param CSVNotifier $csvNotifier
     * @return JsonResponse|Response
     */
    public function notificationsAction(Request $request, CSVNotifier $csvNotifier)
    {
        $logs = $csvNotifier->getAllNotifications();
        $notifications = [];
        foreach ($logs as $log) {
            $notification = sprintf("Your file %s %s.\n",
                $log->getFileName(),
                $log->getIsValid() ? "is valid" : "is invalid"
            );
            $arrData = [
                'notification' => $notification
            ];
            array_push($notifications, $arrData);


        }
        $csvNotifier->setAsReportedNotifications();

        return new JsonResponse($notifications);
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

