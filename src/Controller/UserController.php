<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\FileUploader;
use App\Service\UserService\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @method bool needsRehash(UserInterface $user)
 */
class UserController extends AbstractController
{
    /**
     * @var \App\Service\UserService\UserService
     */
    private $user;

    /**
     * UserController constructor.
     * @param \App\Service\UserService\UserService $user
     */
    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @param AuthenticationUtils $utils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request)
    {
        return $this->render('user/login/index.html.twig', [
            'error' => $this->user->getUserLastAuthenticationError(),
            'last_username' => $this->user->getUserLastUsername(),
        ]);
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $form = $this->createForm(RegisterType::class, $this->user->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->user->registerUser($form);
            return $this->redirectToRoute('login');
        }
        return $this->render('user/register/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
    }
}
