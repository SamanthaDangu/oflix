<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Security\PasswordPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    use CsrfProtectedControllerTrait;

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ReviewRepository $reviewRepository,
        #[CurrentUser] User $user
    ): Response {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = (string) $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } else {
                $email = mb_strtolower(trim($user->getEmail()));
                $pseudo = trim((string) $user->getPseudo());
                $existingByEmail = $userRepository->findOneBy(['email' => $email]);
                $existingByPseudo = $userRepository->findOneBy(['pseudo' => $pseudo]);

                if ($existingByEmail && $existingByEmail->getId() !== $user->getId()) {
                    $form->get('email')->addError(new FormError('Cette adresse email est déjà utilisée.'));
                }

                if ($existingByPseudo && $existingByPseudo->getId() !== $user->getId()) {
                    $form->get('pseudo')->addError(new FormError('Ce pseudo est déjà utilisé.'));
                }

                $newPassword = (string) $form->get('newPassword')->getData();

                if ($newPassword !== '') {
                    if (!PasswordPolicy::isValid($newPassword)) {
                        $form->get('newPassword')->addError(new FormError(
                            'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.'
                        ));
                    } else {
                        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                    }
                }

                if (!$form->getErrors(true)->count()) {
                    $user->setEmail($email);
                    $user->setPseudo($pseudo);
                    $entityManager->flush();
                    $this->addFlash('success', 'Votre profil a été mis à jour.');

                    return $this->redirectToRoute('app_profile');
                }
            }
        }

        return $this->render('security/profile.html.twig', [
            'profile_form' => $form->createView(),
            'reviews' => $reviewRepository->findByUserOrderedByDate($user),
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        #[CurrentUser] User $user
    ): Response {
        $this->assertCsrfTokenValid('delete_account_' . $user->getId(), $request);

        if (!$passwordHasher->isPasswordValid($user, (string) $request->request->get('confirm_password'))) {
            $this->addFlash('error', 'Mot de passe incorrect. Votre compte n\'a pas été supprimé.');

            return $this->redirectToRoute('app_profile');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        // le compte n'existe plus : on invalide nous-mêmes la session au lieu de passer par /logout,
        // sinon Symfony tente de recharger cet utilisateur depuis la base et plante
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été définitivement supprimé.');

        return $this->redirectToRoute('movie_home');
    }
}
