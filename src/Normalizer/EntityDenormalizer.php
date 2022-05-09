<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Entity denormalizer
 */
class EntityDenormalizer implements DenormalizerInterface
{
    /** @var EntityManagerInterface **/
    protected $em;

    // Récupération de service
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Ce denormalizer doit-il s'appliquer sur la donnée courante ?
     * Si oui, on appelle $this->denormalize()
     * 
     * $data => l'id du Genre
     * $type => le type de la classe vers laquelle on souhaite dénormaliser $data
     * 
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        // Est-ce que la classe est de type Entité doctrine ?
        // Est-ce que la donnée fournie est numérique ?
        return strpos($type, 'App\\Entity\\') === 0 && (is_numeric($data));
    }

    /**
     * Cette méthode sera appelée si la condition du dessus est valide
     * 
     * @inheritDoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // Raccourci depuis l'EntityManager pour aller checher une entité
        // ex : $this->em->find('App\Entity\Genre', 1);
        return $this->em->find($class, $data);
    }
}