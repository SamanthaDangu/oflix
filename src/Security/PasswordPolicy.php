<?php

namespace App\Security;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Politique de mot de passe partagee par l'inscription, l'edition de profil
 * et la creation/edition d'utilisateur en back-office, pour eviter que les
 * trois formulaires ne divergent silencieusement.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 8;
    public const COMPLEXITY_REGEX = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/';
    public const COMPLEXITY_MESSAGE = 'Le mot de passe doit contenir une minuscule, une majuscule, un chiffre et un caractère spécial.';
    public const LENGTH_MESSAGE = 'Le mot de passe doit contenir au moins {{ limit }} caractères.';

    public static function regexConstraint(): Regex
    {
        return new Regex(self::COMPLEXITY_REGEX, self::COMPLEXITY_MESSAGE);
    }

    public static function lengthConstraint(): Length
    {
        return new Length(min: self::MIN_LENGTH, minMessage: self::LENGTH_MESSAGE);
    }

    public static function isValid(string $password): bool
    {
        return strlen($password) >= self::MIN_LENGTH && preg_match(self::COMPLEXITY_REGEX, $password) === 1;
    }
}
