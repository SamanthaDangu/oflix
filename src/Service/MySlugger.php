<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class MySlugger
{
    // $slugger va recevoir une instance de InterfaceSlugger
    private $slugger;

    // Paramètre qui permet de décider 
    // Si notre slug doit être en minuscule ou pas
    private $toLower;

    public function __construct(SluggerInterface $slugger, bool $toLower)
    {
        $this->slugger = $slugger;
        $this->toLower = $toLower;
    }

    /**
     * method slugify
     * 
     * prend une chaine en entrée et la kebab-case avant de la rendre
     *
     * @param string $input
     * @return string
     */
    public function slugify(string $input): string
    {
        if ($this->toLower) {
            $slug = $this->slugger->slug($input)->lower();
        }
        else {
            $slug = $this->slugger->slug($input);
        }
        
        return $slug;
    }
}