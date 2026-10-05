<?php

namespace App\DataFixtures;

use App\DataFixtures\Provider\OflixProvider;
use App\Entity\Actor;
use App\Entity\Casting;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Platform;
use App\Entity\Review;
use App\Entity\Season;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Comme Faker propose des méthodes Statiques
        // On n'a pas besoin de faire de l'injection de dépendance
        // https://fakerphp.github.io/#localization
        $faker = Faker::create('fr_FR');


        $oflixProvider = new OflixProvider();

        /************* Genre *************/

        // tableau pour réutiliser les Genre plus tard
        $allGenreEntity = [];
        $genreLabels = [
            'Action',
            'Animation',
            'Aventure',
            'Comédie',
            'Dessin animé',
            'Documentaire',
            'Drame',
            'Espionnage',
            'Famille',
            'Fantastique',
            'Historique',
            'Policier',
            'Romance',
            'Science-fiction',
            'Thriller',
            'Western'
        ];
        foreach ($genreLabels as $genreName) {

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
        for ($i = 0; $i < 200; $i++) {
            $actor = new Actor();
            // https://fakerphp.github.io/formatters/
            $actor->setFirstname($faker->firstName());
            $actor->setLastname($faker->lastName());

            $allActorEntity[] = $actor;

            $manager->persist($actor);
        }

        /************ Platform ************/
        // memes plateformes de streaming (et tmdbId) que MoviesImportCommand::PROVIDERS
        $allPlatformEntity = [];
        $platformLabels = [
            'Netflix' => 8,
            'Prime Video' => 119,
            'Disney+' => 337,
            'Crunchyroll' => 283,
        ];
        foreach ($platformLabels as $platformName => $tmdbId) {
            $platform = new Platform();
            $platform->setName($platformName);
            $platform->setTmdbId($tmdbId);

            $allPlatformEntity[] = $platform;

            $manager->persist($platform);
        }

        /*************** Movie ******************/
        // tableau pour réutiliser les Movie plus tard (casting)
        $allMovieEntity = [];
        for ($i = 1; $i <= 20; $i++) {
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
            // si rand(1, 2) == 1 alors 'Movie' sinon 'Series'
            $type = rand(1, 2) == 1 ? 'Movie' : 'Series';

            $newMovie->setType($type);
            // https://fakerphp.github.io/formatters/date-and-time/#datetimebetween
            $newMovie->setReleaseDate($faker->dateTimeBetween('-20years', 'now'));
            $newMovie->setSummary($faker->sentence());
            // https://fakerphp.github.io/formatters/#fakerprovideren_ustext
            $newMovie->setSynopsis($faker->realText($maxNbChars = 200, $indexSize = 2));

            // très utile pour avoir des images différentes aléatoire pendant les tests
            $newMovie->setPoster('https://picsum.photos/id/' . mt_rand(1, 100) . '/303/424');

            // je veux des saisons pour UNIQUEMENT les séries
            if ($type == 'Series') {
                $nbSeason = rand(1, 5); // entre 1 et 5
                for ($j = 1; $j <= $nbSeason; $j++) //! si 0 saison on passe pas dans la boucle
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

            /***** Ajout des plateformes de streaming *****/
            for ($p = 1; $p <= mt_rand(1, 2); $p++) {
                $randomPlatform = $allPlatformEntity[mt_rand(0, count($allPlatformEntity) - 1)];
                $newMovie->addPlatform($randomPlatform);
            }

            // je garde l'entity pour plus tard
            $allMovieEntity[] = $newMovie;

            $manager->persist($newMovie);
        }
        /** Fin de création de Movie */



        /************ Casting *************/

        for ($i = 0; $i < 100; $i++) {

            // J'ai une liste d'actor : $allActorEntity
            // J'ai une liste de Movie : $allMovieEntity
            // Je vais créer un Casting
            $casting = new Casting();
            $casting->setRole($faker->name());
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

        $allUserEntity = [];
        foreach ($users as $currentUser) {
            $newUser = new User();
            $newUser->setEmail($currentUser['login']);
            $newUser->setRoles([$currentUser['roles']]);

            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $this->hasher->hashPassword(
                $newUser,
                $currentUser['password']
            );
            $newUser->setPassword($hashedPassword);

            $allUserEntity[] = $newUser;

            $manager->persist($newUser);
        }

        /************ Favoris *************/
        // chaque utilisateur ajoute 2 a 4 films au hasard a sa liste
        foreach ($allUserEntity as $user) {
            for ($f = 1; $f <= mt_rand(2, 4); $f++) {
                $randomMovie = $allMovieEntity[mt_rand(0, count($allMovieEntity) - 1)];
                $user->addFavoriteMovie($randomMovie);
            }
        }

        /************ Review **************/
        // chaque utilisateur laisse une critique sur 1 a 3 films distincts
        $reactionChoices = ['smile', 'cry', 'think', 'sleep', 'dream'];
        foreach ($allUserEntity as $user) {
            $reviewedMovieIds = [];
            for ($r = 1; $r <= mt_rand(1, 3); $r++) {
                $randomMovie = $allMovieEntity[mt_rand(0, count($allMovieEntity) - 1)];
                if (isset($reviewedMovieIds[spl_object_id($randomMovie)])) {
                    continue;
                }
                $reviewedMovieIds[spl_object_id($randomMovie)] = true;

                $review = new Review();
                $review->setMovie($randomMovie);
                $review->setUser($user);
                $review->setUsername($user->getDisplayName());
                $review->setEmail($user->getEmail());
                $review->setContent($faker->realText(150, 2));
                $review->setRating((float) mt_rand(1, 5));
                $review->setReactions((array) $faker->randomElements($reactionChoices, mt_rand(1, 2)));
                $review->setWatchedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1years', 'now')));

                $manager->persist($review);
            }
        }

        $manager->flush();
    }
}
