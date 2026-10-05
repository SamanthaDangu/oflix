<?php

namespace App\Controller\Front;

use App\Controller\CsrfProtectedControllerTrait;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: 'main_')]
class MainController extends AbstractController
{
    use CsrfProtectedControllerTrait;

    /**
     * Affiche les films et series ajoutes a la liste de l'utilisateur connecte.
     */
    #[Route('/favorites', name: 'favorites', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function favorites(#[CurrentUser] User $user): Response
    {
        return $this->render('front/main/favorites.html.twig', [
            'favoriteMovies' => $user->getFavoriteMovies(),
        ]);
    }

    /**
     * Bascule le theme de l'interface et revient sur la page precedente.
     */
    #[Route('/theme/toggle', name: 'theme_switcher', methods: ['POST'])]
    public function themeSwitcher(SessionInterface $session, Request $request): Response
    {
        $this->assertCsrfTokenValid('theme_switcher', $request);

        $theme = $session->get('theme', 'netflix');

        if ($theme === 'netflix') {
            $session->set('theme', 'allocine');
        } else {
            $session->set('theme', 'netflix');
        }

        $referer = $request->headers->get('referer');

        if ($referer && str_starts_with($referer, $request->getSchemeAndHttpHost() . '/')) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute("movie_home");
    }
}
