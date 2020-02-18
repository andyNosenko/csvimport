<?php

declare(strict_types=1);

namespace App\Service\UserService;


use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserService
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var AuthenticationUtils
     */
    private $utils;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var EntityManager|EntityManagerInterface
     */
    private $em;

    /**
     * UserService constructor.
     * @param AuthenticationUtils $utils
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FileUploader $fileUploader
     * @param EntityManager $em
     */
    public function __construct(
        AuthenticationUtils $utils,
        UserPasswordEncoderInterface $passwordEncoder,
        FileUploader $fileUploader,
        EntityManagerInterface $em
    ) {
        $this->utils = $utils;
        $this->passwordEncoder = $passwordEncoder;
        $this->fileUploader = $fileUploader;
        $this->user = new User();
        $this->em = $em;
    }

    public function getUserLastAuthenticationError()
    {
        return  $error = $this->utils->getLastAuthenticationError();
    }

    public function getUserLastUsername()
    {
        return  $lastUserName = $this->utils->getLastUsername();
    }

    public function registerUser($form)
    {
        $this->user->setRoles(['ROLE_USER']);
        $this->user = $form->getData();
        $image = $form['image']->getData();
        if ($image) {
            $imageFileName = $this->fileUploader->upload($image);
            $this->user->setImage($imageFileName);
        }
        $this->user->setPassword($this->passwordEncoder->encodePassword($this->user, $this->user->getPassword()));
        $this->em->persist($this->user);
        $this->em->flush();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }



}