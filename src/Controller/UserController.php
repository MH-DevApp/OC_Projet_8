<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list', methods: ['GET'])]
    public function listAction(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    #[Route('/users/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function createAction(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $password */
            $password = $user->getPassword();
            /** @var bool $isAdmin */
            $isAdmin = $form->get('isAdmin')->getData();

            $password = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user->setPassword($password);

            // Anomaly correction => Choosing a role for a user (new feature)
            $user->isAdmin($isAdmin);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'L\'utilisateur a bien été ajouté.'
            );

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editAction(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        User $user
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $password */
            $password = $user->getPassword();
            /** @var bool $isAdmin */
            $isAdmin = $form->get('isAdmin')->getData();

            $password = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user->setPassword($password);

            // Anomaly correction => Choosing a role for a user (new feature)
            $user->isAdmin($isAdmin);

            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a bien été modifié');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
