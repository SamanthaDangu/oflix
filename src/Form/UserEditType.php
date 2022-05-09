<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;


class UserEditType extends AbstractType
{
    /** NE SERT PLUS DU TOUT, FUSION AVEC USERTYPE 
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                // pour que la valeur ne soit pas nulle quand le formulaire va lire le contenu
                'empty_data' => ''
            ])
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    // Libellé => Valeur
                    'Utilisateur' => 'ROLE_USER',
                    'Gestionnaire' => 'ROLE_MANAGER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                // Choix multiple => Tableau ;)
                'multiple' => true,
                // On veut des checkboxes !
                'expanded' => true,
            ])
            ->add('password', PasswordType::class, [
                // pour que la valeur ne soit pas nulle quand le formulaire va lire le contenu
                'empty_data' => '',
                // on détache le mot de passe du mapping auto
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Laissez vide si inchangé'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }
    */
}
