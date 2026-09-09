<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ReviewType extends AbstractType
{
    /**
     * Création et paramétrage du formulaire
     *
     * @link https://symfony.com/doc/current/best_practices.html#define-your-forms-as-php-classes
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Critique'
            ])
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    'Excellent' => 5,
                    'Très bon' => 4,
                    'Bon' => 3,
                    'Peut mieux faire' => 2,
                    'A éviter' => 1,
                ],
                'placeholder' => 'Votre appréciation...',
                'label' => false,
            ])
            ->add('reactions', ChoiceType::class, [
                'label' => 'Ce film vous a fait...',
                'row_attr' => ['class' => 'reactions-picker'],
                'choices' => [
                    'Rire' => 'smile',
                    'Pleurer' => 'cry',
                    'Réfléchir' => 'think',
                    'Dormir' => 'sleep',
                    'Rêver' => 'dream',
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('watchedAt', DateType::class, [
                // renders it as a single text box
                'widget' => 'single_text',
                'label' => 'Vous avez vu ce film le :',
                'input' => 'datetime_immutable',
                // https://symfony.com/doc/current/reference/forms/types/date.html#format
                // si on touche trop au format, il n'apprecie pas
                // mais comme le widget fait le rendu au format français
                // 'format' => 'yyyy-MM-dd',
            ])
            // Le film est associé dans le contrôleur depuis la route.
            // Les champs username et email viennent du compte connecté.
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
