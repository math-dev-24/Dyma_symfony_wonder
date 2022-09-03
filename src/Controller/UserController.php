<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function userProfil(User $user): Response
    {
        $currentUser = $this->getUser();
        if($currentUser === $user){
            return $this->redirectToRoute("current_user");
        }
        return $this->render('user/user.html.twig',
        ['user' => $user]);
    }

    #[Route('/user', name: 'current_user')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function CurrentUserProfile(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Request $request): Response
    {
        $user = $this->getUser();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->remove("password");
        $userForm->add('newPassword',PasswordType::class, ['label' => 'Nouveau password :', "required" => false]);
        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()){
            $newPassword = $user->getNewPassword();
            if($newPassword){
                $hash = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hash);
            }
            $entityManager->flush();
            $this->addFlash("success", "Modification sauvegardées !");
        }

        return $this->render('user/index.html.twig', [
            "form" => $userForm->createView()
        ]);
    }

    #[Route('/my-question', name: 'user_question')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public  function myQuestion(): Response
    {
        return $this->render("user/myQuestion.html.twig");
    }
    #[Route('/my-comment', name: 'user_comment')]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public  function myComment(): Response
    {
        return $this->render("user/myComment.html.twig");
    }
}
