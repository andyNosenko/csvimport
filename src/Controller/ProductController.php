<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CSVFileType;
use App\Service\CSVImportWorker;
use App\Service\CSVMailSender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function csvImportAction(Request $request, CSVImportWorker $csvImportWorker, CSVMailSender $csvMailSender)
    {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $extension = $file->guessExtension();
            if ($extension == 'csv' || $extension == 'txt') {
                $destination = $this->getParameter('kernel.project_dir') . '/src/Data';

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                if ($extension == "txt") {
                    $fileName = $originalFilename . '.' . 'csv';
                } else {
                    $fileName = $originalFilename . '.' . $extension;
                }
                $file->move($destination, $fileName);

                $csvImportWorker->importProducts($destination . '/' . $fileName, false);

                $csvMailSender->sendEmail($csvImportWorker);

                return $this->render("csv/report.html.twig", [
                    'total' => $csvImportWorker->total,
                    'skipped' => $csvImportWorker->skipped,
                    'processed' => $csvImportWorker->processed,
                    'products' => $csvImportWorker->products,
                ]);
            } else {
                return $this->render("csv/fileExtensionError.html.twig");
            }
        }
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

