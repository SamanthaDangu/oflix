<?php

namespace App\Command;

use App\Repository\MovieRepository;
use App\Service\MySlugger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MovieSlugifyCommand extends Command
{
    protected static $defaultName = 'app:movies:slugify';
    protected static $defaultDescription = 'Slugifies movies titles in the Database';

    // Pour interagir avec les films dans la BDD
    private $movieRepository;
    // Pour pouvoir utiliser MySlugger
    private $mySlugger;
    // Pour interagir avec l'entity manager
    private $entityManager;


    /**
     * on injecte dès la création de l'instance de cette classe
     * les services dont on a besoin
     * On les stocke dans des prioriétés privées 
     * 
     * Ensuite, on exécute le constructeur de la classe parente : Command
     *
     * @param MovieRepository $movieRepository
     * @param MySlugger $mySlugger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MovieRepository $movieRepository,
                                MySlugger $mySlugger,
                                EntityManagerInterface $entityManager)
    {
        $this->movieRepository = $movieRepository;
        $this->mySlugger = $mySlugger;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // j'utilise une classe qui permet de faire des entrées/sorties 
        // en costard/cravate
        $io = new SymfonyStyle($input, $output);

        $io->info('attache ta ceinture, ça commence...');

        // Récupérer tous les films (via MovieRepository)
        $movies = $this->movieRepository->findAll();

        // créer le slug à partir du titre pour chaque film
        // Pour chaque film
        foreach ($movies as $movie) {
            $io->info('Titre à Slugifier : ' . $movie->getTitle());

            // On slugifie le titre avec notre service MySlugger
            //! le preUpdate de doctrine ne seras PAS lancé car on ne modifie rien dans notre entity
            //! pour l'optimisation, doctrine ne fait que des requetes que si besoin
            //! donc si on ne touche pas à notre entity, doctrine ne ferat pas d'update, donc pas d'event 
            $movie->setSlug($this->mySlugger->slugify($movie->getTitle()));

            $io->info('Résultat : ' . $movie->getSlug());

            // le persist n'est pas obligatoire dans ce contexte
            //$this->entityManager->persist($movie);
        }

        // enregistrer le slug
        $this->entityManager->flush();

        $io->info('déjà terminé ! tu peux prendre la porte à gauche...');
        return Command::SUCCESS;

    }
}
