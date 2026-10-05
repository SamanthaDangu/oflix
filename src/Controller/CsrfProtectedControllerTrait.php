<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Verification CSRF partagee par les controleurs front et back-office,
 * pour garantir un comportement identique (rejet via AccessDeniedException)
 * partout plutot que trois variantes divergentes (exception, no-op, flash).
 */
trait CsrfProtectedControllerTrait
{
    private function assertCsrfTokenValid(string $tokenId, Request $request): void
    {
        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
    }
}
