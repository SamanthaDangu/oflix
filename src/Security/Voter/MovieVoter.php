<?php

namespace App\Security\Voter;

use DateTime;
use Symfony\Bundle\WebProfilerBundle\Csp\NonceGenerator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MovieVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        // si l'attribut commence par MOVIE_ alors on veut voter
        if (
            $attribute === "MODIFICATION_DU_MOVIE"
        || $attribute === "AJOUT_DU_MOVIE")
        {
            return true;
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param string $attribute
     * @param Movie $subject
     * @param TokenInterface $token
     * @return boolean
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /* @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        //dump($attribute);
        if ($attribute === "MODIFICATION_DU_MOVIE")
        {
            // on ne peut modifier que le film qui a le nom schrek 
            // que si on est l'utilisateur manager@manager.com
            //dump($subject->getTitle());
            //dump($user->getUserIdentifier());
            if ($subject->getTitle() === 'Shrek' && $user->getUserIdentifier() !== 'manager@manager.com')
            {
                return false;
            }
            // // si il est plus de 14h on n'a pas le droit de modifier
            // if (date_format(new DateTime(), 'Hi') > 1400)
            // {
            //     return false;
            // }
        }


        return true;
    }
}
