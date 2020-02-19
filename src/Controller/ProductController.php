<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CSVFileType;
use App\Form\EditProductType;
use App\Service\CSVNotifier;
use App\Service\DBProductExporter;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @Route("/importfile", options={"expose"=true}, name="importfile")
     * @param Request $request
     * @param DBProductExporter $dbProductExporter
     * @param AuthorizationCheckerInterface $authChecker
     * @return Response
     */
    public function csvImportAction(
        Request $request,
        DBProductExporter $dbProductExporter
    ) {
        $form = $this->createForm(CSVFileType::class);
        $form->handleRequest($request);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form["file"]->getData();
            $isTest = $form["test"]->getData();

            $destination = $this->getParameter('uploadDirectory');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filePath = $destination . $originalFilename . ".csv";
            $file->move($destination, $originalFilename . ".csv");

            $this->producer->add($filePath, $user->getId());
        }

        $productsView = $dbProductExporter->ReturnProducts($request);
        return $this->render('csv/index.html.twig', [
            'form' => $form->createView(),
            'productsView' => $productsView,
        ]);
    }

//    /**
//     * @Route("/edit/{id}", options={"expose"=true}, name="edit_product")
//     * @ParamConverter("product", class="SensioBlogBundle:Product")
//     * @param Request $request
//     * @param Product $product
//     * @param EntityManagerInterface $em
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
//     * @throws \Exception
//     */
//    public function editProductAction(Request $request, int $id, EntityManagerInterface $em)
//    {
//        $product = $em->getRepository(Product::class)->findOneBy([
//            'id' => $id,
//        ]);
//        $form = $this->createForm(EditProductType::class, $product, [
//            'action' => $this->generateUrl('edit_product', [
//                'id' => $product->getId()
//            ]),
//            'method' => 'GET',
//        ]);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $product = $form->getData();
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($product);
//            $em->flush();
//            return $this->redirectToRoute('importfile');
//        }
//
//        return $this->render('csv/edit_product.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }

    /**
     * @Route("/edit/{id}", options={"expose"=true}, name="edit_product", requirements={"id"="\d+"})
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editProductAction(int $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($id);

        if (null === $product) {
            throw new NotFoundHttpException("L'étudiant d'id " . $id . " n'existe pas.");
        }

        $form = $this->createForm(EditProductType::class, $product);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            return $this->redirectToRoute('importfile');
        }

        return $this->render('csv/edit_product.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),

        ));
    }

    /**
     * @Route("/product/{id}", options={"expose"=true}, name="get_product_by_id")
     * @param Request $request
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse|Response
     */
    public function getProductAction(Request $request, int $id, EntityManagerInterface $em)
    {
        $product = $em->getRepository(Product::class)->findOneBy([
            'id' => $id,
        ]);
        $data = [
            'product_code' => $product->getProductCode(),
            'product_name' => $product->getProductName(),
            'product_description' => $product->getProductDescription(),
            'stock' => $product->getStock(),
            'cost' => $product->getCost(),
            'discontinued' => $product->getDiscontinued(),
            'category' => $product->getCategory()->getName()
        ];
        return new JsonResponse($data);
    }

    /**
     * @Route("/save/{id}", options={"expose"=true}, name="save_product_by_id")
     * @param Request $request
     * @param int $id
     * @param EntityManagerInterface $em
     * @return JsonResponse|Response
     */
    public function saveProductAction(Request $request, int $id, EntityManagerInterface $em)
    {
        $product = $em->getRepository(Product::class)->findOneBy([
            'id' => $id,
        ]);

        if ($request->isMethod('POST')) {
            $productCode = $request->get('product_code');
            $productName = $request->get('product_name');
            $productDescription = $request->get('product_description');
            $stock = $request->get('stock');
            $cost = $request->get('cost');
            $discontinued = $request->get('discontinued');
            $category = $request->get('category');
        }

        $categoryId = $em->getRepository(Category::class)->findOneBy([
            'name' => $category,
        ]);

        $product->setProductCode($productCode);
        $product->setProductName($productName);
        $product->setProductDescription($productDescription);
        $product->setStock((int)$stock);
        $product->setCost((int)$cost);
        $product->setDiscontinued($discontinued);
        $product->setCategory($categoryId);
        $em->persist($product);
        $em->flush();

        return new JsonResponse(
            'Saved successfully!',
            200,
            [
                'Content-Type' => 'text/html'
            ]
        );
    }

    /**
     * @Route("/notifications", options={"expose"=true}, name="notifications")
     * @param Request $request
     * @param CSVNotifier $csvNotifier
     * @return JsonResponse|Response
     */
    public function notificationsAction(Request $request, CSVNotifier $csvNotifier)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $logs = $csvNotifier->getAllUserNotifications($user->getId());
        $notifications = [];
        foreach ($logs as $log) {
            $notification = sprintf(
                "Your file %s %s.\n",
                $log->getFileName(),
                $log->getIsValid() ? "is valid" : "is invalid"
            );
            $arrData = [
                'notification' => $notification
            ];
            $notifications[] = $arrData;
        }
        $csvNotifier->setAsReportedUserNotifications($logs);

        return new JsonResponse($notifications);
    }

    /**
     * @Route("/view", options={"expose"=true}, name="ProductsView")
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
