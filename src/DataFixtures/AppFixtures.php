<?php

namespace App\DataFixtures;

use App\DataFixtures\Provider\OflixProvider;
use App\Entity\Actor;
use App\Entity\Casting;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Season;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\MySlugger;

class AppFixtures extends Fixture
{

    private $connexion;
    private $hasher;
    private $sluggifier;
    // la propriété $slugger va recevoir une instance du service SluggerInterface
    private $slugger;


    // comme je ne peut pas modifier les paramètres de la méthode Load() à cause de l'héritage
    // j'utilise mon constructeur pour utiliser l'injection de dépendence
    // et demander à ce que le FW me fournisse l'objet connection
    public function __construct(Connection $connexion, UserPasswordHasherInterface $hasher, MySlugger $mySlugger)
    {
        // cet Objet nous permet d'exécuter des requete SQL
        $this->connexion = $connexion;
        $this->hasher = $hasher;
        // cet Objet va nous permettre d'utiliser les méthodes publiques
        // de notre service MySlugger
        $this->slugger = $mySlugger;
    }
    
    public function setSlugger(SluggerInterface $slugger)
    {

    }

    // On sépare un peu notre code
    private function truncate()
    {
        //  on désactive la vérification des FK
        // Sinon les truncate ne fonctionne pas.
        $this->connexion->executeQuery('SET foreign_key_checks = 0');

        // la requete TRUNCATE remet l'auto increment à 1
        $this->connexion->executeQuery('TRUNCATE TABLE casting');
        $this->connexion->executeQuery('TRUNCATE TABLE genre');
        $this->connexion->executeQuery('TRUNCATE TABLE movie');
        $this->connexion->executeQuery('TRUNCATE TABLE movie_genre');
        $this->connexion->executeQuery('TRUNCATE TABLE actor');
        $this->connexion->executeQuery('TRUNCATE TABLE season');
        $this->connexion->executeQuery('TRUNCATE TABLE user');
    }

    public function load(ObjectManager $manager): void
    {
        // on vide les tables avant de commencer
        $this->truncate();

        // Comme Faker propose des méthodes Statiques
        // On n'a pas besoin de faire de l'injection de dépendance
        // https://fakerphp.github.io/#localization
        $faker = Faker::create('fr_FR');


        $oflixProvider = new OflixProvider();

        /************* Genre *************/

        // tableau pour réutiliser les Genre plus tard
        $allGenreEntity = [];
        $genresTexte = [
            'Action', 'Animation', 'Aventure', 'Comédie', 'Dessin animé', 'Documentaire', 'Drame', 'Espionnage', 'Famille',
            'Fantastique', 'Historique', 'Policier', 'Romance', 'Science-fiction', 'Thriller', 'Western'
          ];
        foreach ($genresTexte as $genreName) {

            // Nouveau genre
            $genre = new Genre();
            $genre->setName($genreName);

            // On l'ajoute à la liste pour usage ultérieur
            $allGenreEntity[] = $genre;

            // On persiste
            $manager->persist($genre);
        } 

        /************ Actor ************/
        // tableau pour réutiliser les Actor plus tard (casting)
        $allActorEntity = [];
        for ($i=0; $i < 200; $i++) { 
            $actor = new Actor();
            // https://fakerphp.github.io/formatters/
            $actor->setFirstname($faker->firstName());
            $actor->setLastname($faker->lastName());

            $allActorEntity[] = $actor;

            $manager->persist($actor);
        }


        /*************** Movie ******************/
        // tableau pour réutiliser les Movie plus tard (casting)
        $allMovieEntity = [];
        for ($i = 1; $i<= 20; $i++)
        {
            // je veux pouvoir creer un Movie
            $newMovie =  new Movie();
            // https://fakerphp.github.io/formatters/text-and-paragraphs/#words
            //$newMovie->setTitle($faker->words(3, true));
            $newMovie->setTitle($oflixProvider->movieTitle());

            // Maintenant qu'on a ajouté le titre,
            // on peut le récupérer pour le sluggifier
            
            //! calcul du slug => fait dans le Listener
            // $slug = $this->slugger->slugify($newMovie->getTitle());

            // On remplie le champ de Movie
            //$newMovie->setSlug($slug);

            $newMovie->setDuration(rand(30, 180));

            // rand(1, 2) => soit 1 soit 2
            // si rand(1, 2) == 1 alors 'Film' sinon 'Série'
            $type = rand(1, 2) == 1 ? 'Film' : 'Série';

            $newMovie->setType($type);
            // https://fakerphp.github.io/formatters/date-and-time/#datetimebetween
            $newMovie->setReleaseDate($faker->dateTimeBetween('-20years', 'now'));
            $newMovie->setSummary($faker->sentence());
            // https://fakerphp.github.io/formatters/#fakerprovideren_ustext
            $newMovie->setSynopsis($faker->realText($maxNbChars = 200, $indexSize = 2));
            
            // très utile pour avoir des images différentes aléatoire pendant les tests
            $newMovie->setPoster('https://picsum.photos/id/'.mt_rand(1, 100).'/303/424');
            
            // je veux des saisons pour UNIQUEMENT les séries
            if ($type == 'Série')
            {
                $nbSeason = rand(1, 5); // entre 1 et 5
                for ($j = 1; $j <= $nbSeason; $j++ ) //! si 0 saison on passe pas dans la boucle
                {
                    $newSeason = new Season();
                    $newSeason->setNumber($j);
                    $newSeason->setEpisodesNumber(mt_rand(6, 24));
                    
                    // ne pas oublier de faire un persist
                    // pour que le manager prenne connaisance de ce nouvel objet
                    $manager->persist($newSeason);
                    
                    $newMovie->addSeason($newSeason);
                }
            }

            /***** Ajout du genre *****/
            // On ajoute de 1 à 3 genres au hasard pour chaque film
            for ($g = 1; $g <= mt_rand(1, 3); $g++) {
                // Les doublons sont gérer pas la méthode addGenre()
                $randomGenre = $allGenreEntity[mt_rand(0, count($allGenreEntity) - 1)];
                $newMovie->addGenre($randomGenre);
            }
            
            $newMovie->setRating($faker->randomFloat(1, 0, 5));

            // je garde l'entity pour plus tard
            $allMovieEntity[] = $newMovie;

            $manager->persist($newMovie);

        }
        /** Fin de création de Movie */

        

        /************ Casting *************/

        for ($i=0; $i < 100; $i++) {
            
            // J'ai une liste d'actor : $allActorEntity
            // J'ai une liste de Movie : $allMovieEntity
            // Je vais créer un Casting
            $casting = new Casting();
            $casting->setRole($faker->name());
            $casting->setCreditOrder($i);
            // Je vais lui donner un movie depuis la liste
            $randomMovie = $allMovieEntity[mt_rand(0, count($allMovieEntity) - 1)];
            $casting->setMovie($randomMovie);
            // Je vais lui donner un actor depuis la liste
            $randomActor = $allActorEntity[mt_rand(0, count($allActorEntity) - 1)];
            $casting->setActor($randomActor);
            // je persist
            $manager->persist($casting);
            // je vais répeter ça Random fois
        }

        $users = [
            [
                'login' => 'admin@admin.com',
                'password' => 'admin',
                'roles' => 'ROLE_ADMIN',
            ],
            [
                'login' => 'manager@manager.com',
                'password' => 'manager',
                'roles' => 'ROLE_MANAGER',
            ],
            [
                'login' => 'user@user.com',
                'password' => 'user',
                'roles' => 'ROLE_USER',
            ],
        ];

        foreach ($users as $currentUser)
        {
            $newUser = new User();
            $newUser->setEmail($currentUser['login']);
            $newUser->setRoles([$currentUser['roles']]);

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->hasher->hashPassword(
                $newUser,
                $currentUser['password']
            );
            $newUser->setPassword($hashedPassword);

            $manager->persist($newUser);
        }

        $manager->flush();
    }
}
