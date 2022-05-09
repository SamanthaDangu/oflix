<?php

namespace App\EventListener;

use App\Entity\Movie;
use App\Service\MySlugger;

class MovieListener
{
    private $slugger;

    public function __construct(MySlugger $slugger)
    {
        $this->slugger = $slugger;    
    }

    public function updateSlug(Movie $movie)
    {
        // calcul du slug
        $slug = $this->slugger->slugify($movie->getTitle());
        // modification du slug dans l'entity
        $movie->setSlug($slug);

        // version une ligne
        // $movie->setSlug($this->slugger->slugify($movie->getTitle()));
    }
}