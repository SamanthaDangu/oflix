<?php

namespace App\Command;

use App\Entity\Actor;
use App\Entity\Casting;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Season;
use App\Repository\ActorRepository;
use App\Repository\CastingRepository;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use App\Repository\SeasonRepository;
use App\Service\TmdbApi;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe des films et series depuis TMDb (listes "popular" et "top_rated"),
 * avec genres, casting et saisons. Relancable : les films deja importes
 * (identifies par leur tmdbId) sont mis a jour plutot que dupliques.
 */
#[AsCommand(name: 'app:movies:import', description: 'Importe des films et series depuis TMDb (popular + top_rated)')]
class MoviesImportCommand extends Command
{
    private const MEDIA_TYPES = [
        'movie' => 'Film',
        'tv' => 'Série',
    ];
    private const LISTS = ['popular', 'top_rated'];
    private const MAX_CAST = 10;

    /**
     * Identifiants TMDb des plateformes de streaming (region France).
     * "adn" n'existe sur TMDb qu'au travers d'Amazon Channel (pas d'app standalone trackee).
     */
    private const PROVIDERS = [
        'netflix' => 8,
        'prime' => 119,
        'disney' => 337,
        'crunchyroll' => 283,
        'adn' => 2173,
    ];

    private $entityManager;
    private $movieRepository;
    private $genreRepository;
    private $actorRepository;
    private $castingRepository;
    private $seasonRepository;
    private $tmdbApi;

    public function __construct(
        ManagerRegistry $doctrine,
        MovieRepository $movieRepository,
        GenreRepository $genreRepository,
        ActorRepository $actorRepository,
        CastingRepository $castingRepository,
        SeasonRepository $seasonRepository,
        TmdbApi $tmdbApi
    ) {
        $this->entityManager = $doctrine->getManager();
        $this->movieRepository = $movieRepository;
        $this->genreRepository = $genreRepository;
        $this->actorRepository = $actorRepository;
        $this->castingRepository = $castingRepository;
        $this->seasonRepository = $seasonRepository;
        $this->tmdbApi = $tmdbApi;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('pages', null, InputOption::VALUE_REQUIRED, 'Nombre de pages a importer par liste', 5);
        $this->addOption(
            'providers',
            null,
            InputOption::VALUE_REQUIRED,
            'Filtre sur des plateformes de streaming (region France), separees par des virgules : ' . implode(', ', array_keys(self::PROVIDERS))
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pages = (int) $input->getOption('pages');
        $providerIds = $this->resolveProviderIds($input->getOption('providers'), $io);

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $genreCache = [];
        $actorCache = [];
        $processedCount = 0;

        foreach (self::MEDIA_TYPES as $mediaType => $typeLabel) {
            $io->section(sprintf('%s (%s)', $typeLabel, $mediaType));

            $ids = $providerIds
                ? $this->collectIdsFromProviders($mediaType, $providerIds, $pages, $io)
                : $this->collectIdsFromLists($mediaType, $pages, $io);

            $io->info(sprintf('%d titres uniques a traiter', count($ids)));
            $io->progressStart(count($ids));

            foreach (array_keys($ids) as $tmdbId) {
                $io->progressAdvance();
                usleep(250000);

                $details = $this->tmdbApi->fetchDetails($mediaType, $tmdbId);
                if ($details === null) {
                    $io->warning(sprintf('Echec de récupération des détails pour %s/%d', $mediaType, $tmdbId));
                    $stats['skipped']++;
                    continue;
                }

                if (!$this->importMovie($mediaType, $typeLabel, $tmdbId, $details, $genreCache, $actorCache, $stats)) {
                    continue;
                }

                $processedCount++;
                if ($processedCount % 20 === 0) {
                    $this->entityManager->flush();
                }
            }

            $io->progressFinish();
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Import termine : %d crees, %d mis a jour, %d ignores',
            $stats['created'],
            $stats['updated'],
            $stats['skipped']
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, true> ids TMDb en cles
     */
    private function collectIdsFromLists(string $mediaType, int $pages, SymfonyStyle $io): array
    {
        $ids = [];
        foreach (self::LISTS as $listName) {
            $ids += $this->collectIds(
                fn (int $page) => $this->tmdbApi->fetchList($mediaType, $listName, $page),
                $pages,
                $io,
                sprintf('%s/%s', $mediaType, $listName)
            );
        }

        return $ids;
    }

    /**
     * @return array<int, true> ids TMDb en cles
     */
    private function collectIdsFromProviders(string $mediaType, string $providerIds, int $pages, SymfonyStyle $io): array
    {
        return $this->collectIds(
            fn (int $page) => $this->tmdbApi->fetchDiscover($mediaType, $providerIds, $page),
            $pages,
            $io,
            sprintf('%s/discover', $mediaType)
        );
    }

    /**
     * @param callable(int): (array|null) $fetchPage
     *
     * @return array<int, true> ids TMDb en cles
     */
    private function collectIds(callable $fetchPage, int $pages, SymfonyStyle $io, string $errorLabel): array
    {
        $ids = [];
        for ($page = 1; $page <= $pages; $page++) {
            $items = $fetchPage($page);
            if ($items === null) {
                $io->warning(sprintf('Echec de récupération de %s page %d', $errorLabel, $page));
                continue;
            }
            foreach ($items as $item) {
                if (isset($item['id'])) {
                    $ids[$item['id']] = true;
                }
            }
        }

        return $ids;
    }

    private function resolveProviderIds(?string $providersOption, SymfonyStyle $io): ?string
    {
        if (!$providersOption) {
            return null;
        }

        $ids = [];
        foreach (explode(',', $providersOption) as $key) {
            $key = strtolower(trim($key));
            if ($key === '') {
                continue;
            }
            if (!isset(self::PROVIDERS[$key])) {
                $io->warning(sprintf('Plateforme inconnue ignorée : "%s" (valeurs possibles : %s)', $key, implode(', ', array_keys(self::PROVIDERS))));
                continue;
            }
            $ids[] = self::PROVIDERS[$key];
        }

        return $ids ? implode('|', $ids) : null;
    }

    private function importMovie(string $mediaType, string $typeLabel, int $tmdbId, array $details, array &$genreCache, array &$actorCache, array &$stats): bool
    {
        $title = $mediaType === 'movie' ? ($details['title'] ?? null) : ($details['name'] ?? null);
        $releaseDateRaw = $mediaType === 'movie' ? ($details['release_date'] ?? null) : ($details['first_air_date'] ?? null);
        $posterUrl = $this->tmdbApi->buildPosterUrl($details['poster_path'] ?? null);
        $genres = $details['genres'] ?? [];

        if (!$title || !$releaseDateRaw || !$posterUrl || empty($genres)) {
            $stats['skipped']++;
            return false;
        }

        try {
            $releaseDate = new DateTime($releaseDateRaw);
        } catch (\Exception $e) {
            $stats['skipped']++;
            return false;
        }

        $movie = $this->movieRepository->findOneBy(['tmdbId' => $tmdbId, 'type' => $typeLabel]);
        $isNew = $movie === null;
        if ($isNew) {
            $movie = new Movie();
            $movie->setTmdbId($tmdbId);
        }

        $movie->setTitle($title);
        $movie->setReleaseDate($releaseDate);
        $movie->setType($typeLabel);
        $movie->setSynopsis($details['overview'] ?? '');
        $movie->setSummary(!empty($details['tagline']) ? $details['tagline'] : mb_substr($details['overview'] ?? '', 0, 255));
        $movie->setRating(isset($details['vote_average']) ? (float) $details['vote_average'] : null);
        $movie->setPoster($posterUrl);

        $duration = $mediaType === 'movie'
            ? ($details['runtime'] ?? 0)
            : ($details['episode_run_time'][0] ?? 0);
        $movie->setDuration((int) $duration);

        foreach ($genres as $genreData) {
            $genreName = $genreData['name'] ?? null;
            if (!$genreName) {
                continue;
            }
            if (!isset($genreCache[$genreName])) {
                $genre = $this->genreRepository->findOneBy(['name' => $genreName]);
                if (!$genre) {
                    $genre = new Genre();
                    $genre->setName($genreName);
                    $this->entityManager->persist($genre);
                }
                $genreCache[$genreName] = $genre;
            }
            $movie->addGenre($genreCache[$genreName]);
        }

        $this->entityManager->persist($movie);

        $this->importCasting($movie, $isNew, $details['credits']['cast'] ?? [], $actorCache);

        if ($mediaType === 'tv') {
            $this->importSeasons($movie, $isNew, $details['seasons'] ?? []);
        }

        $stats[$isNew ? 'created' : 'updated']++;

        return true;
    }

    /**
     * @param array<string, Actor> $actorCache cle = "tmdb:<id>" ou "name:<prenom>|<nom>", partagee sur tout le run
     */
    private function importCasting(Movie $movie, bool $movieIsNew, array $cast, array &$actorCache): void
    {
        $existingActorIds = [];
        if (!$movieIsNew) {
            foreach ($this->castingRepository->findBy(['movie' => $movie]) as $existingCasting) {
                $existingActorIds[$existingCasting->getActor()->getId()] = true;
            }
        }

        foreach (array_slice($cast, 0, self::MAX_CAST) as $castMember) {
            $name = trim($castMember['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $actorTmdbId = $castMember['id'] ?? null;
            [$firstname, $lastname] = $this->splitName($name);
            $cacheKey = $actorTmdbId ? 'tmdb:' . $actorTmdbId : 'name:' . $firstname . '|' . $lastname;

            if (!isset($actorCache[$cacheKey])) {
                $actor = $actorTmdbId ? $this->actorRepository->findOneBy(['tmdbId' => $actorTmdbId]) : null;
                if (!$actor) {
                    $actor = $this->actorRepository->findOneBy(['firstname' => $firstname, 'lastname' => $lastname]);
                }

                if (!$actor) {
                    $actor = new Actor();
                    $actor->setFirstname($firstname);
                    $actor->setLastname($lastname);
                    if ($actorTmdbId) {
                        $actor->setTmdbId($actorTmdbId);
                    }
                    $this->entityManager->persist($actor);
                } elseif ($actorTmdbId && !$actor->getTmdbId()) {
                    $actor->setTmdbId($actorTmdbId);
                }

                $actorCache[$cacheKey] = $actor;
            }

            $actor = $actorCache[$cacheKey];

            if (!isset($existingActorIds[$actor->getId()])) {
                $casting = new Casting();
                $casting->setMovie($movie);
                $casting->setActor($actor);
                $casting->setRole($castMember['character'] ?? '');
                $this->entityManager->persist($casting);
                $existingActorIds[$actor->getId()] = true;
            }
        }
    }

    private function importSeasons(Movie $movie, bool $movieIsNew, array $seasons): void
    {
        $existingSeasonsByNumber = [];
        if (!$movieIsNew) {
            foreach ($this->seasonRepository->findBy(['movie' => $movie]) as $existingSeason) {
                $existingSeasonsByNumber[$existingSeason->getNumber()] = $existingSeason;
            }
        }

        foreach ($seasons as $seasonData) {
            $seasonNumber = $seasonData['season_number'] ?? null;
            if ($seasonNumber === null || $seasonNumber < 1) {
                continue;
            }

            $season = $existingSeasonsByNumber[$seasonNumber] ?? null;
            if (!$season) {
                $season = new Season();
                $season->setNumber($seasonNumber);
                $season->setMovie($movie);
                $this->entityManager->persist($season);
            }
            $season->setEpisodesNumber($seasonData['episode_count'] ?? 0);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = explode(' ', $name, 2);

        return [$parts[0], $parts[1] ?? $parts[0]];
    }
}
