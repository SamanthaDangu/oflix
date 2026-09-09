<?php

namespace App\Form;

use App\Entity\Actor;
use App\Entity\Casting;
use App\Entity\Movie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CastingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role')
            ->add('movie', EntityType::class, [
                'class' => Movie::class,
                'choice_label' => 'getSomethingForCastingForm'
            ])
            ->add('actor', EntityType::class, [
                'class' => Actor::class,
                // je lui donne le nom d'une fonction de mon entity
                // dans laquelle je fait ce que je veux
                'choice_label' => 'getFullname',
                /* même solution avec une fonction anonyme
                'choice_label' => function($actor){
                    return $actor->getFirstname().' '.$actor->getLastname();
                }
                */
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Casting::class,
        ]);
    }
}
