<?php
declare(strict_types=1);
namespace App\Controller;


use App\Form\CSVFileType;
use App\Service\CSVImportWorker;
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
     * @return Response
     */
    public function csvImportAction(Request $request, CSVImportWorker $csvImportWorker)
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
                    $fileName = $originalFilename . '.' . $file->guessExtension();
                }
                $file->move($destination, $fileName);

                $csvImportWorker->importProducts($destination . '/' . $fileName, false);

                $csvImportWorker->sendEmail();

                return $this->render("csv/report.html.twig", [
                    'total' => $csvImportWorker->getTotal(),
                    'skipped' => $csvImportWorker->getSkipped(),
                    'processed' => $csvImportWorker->getProcessed(),
                ]);
            } else {
                return new Response('<h1>Only csv files allowed!</h1>');
            }
        }
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
