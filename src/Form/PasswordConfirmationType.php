<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Repeated password field partage entre l'inscription et l'edition de profil,
 * pour eviter que le type de champ et le message d'erreur ne divergent
 * silencieusement entre les deux formulaires.
 */
class PasswordConfirmationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'type' => PasswordType::class,
            'mapped' => false,
            'invalid_message' => 'Les mots de passe doivent être identiques.',
        ]);
    }

    public function getParent(): string
    {
        return RepeatedType::class;
    }
}
