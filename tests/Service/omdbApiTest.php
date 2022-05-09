<?php

namespace App\Tests\Service;

use App\Service\OmdbApi;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class omdbApiTest extends KernelTestCase
{
    public function testAllGood(): void
    {
        // (1) on démarre le FW Symfony
        $kernel = self::bootKernel();

        // assertSame( valeurAttendue, valeurATester)
        // @link https://phpunit.readthedocs.io/fr/latest/assertions.html
        // $this->assertSame('test', $kernel->getEnvironment());

        // avec la méthode statique getContainer()->get(FQCN_Class)
        // on récupère notre Service comme si on l'avait reçut par injection de dépendance
        // on doit faire comme ça parce que nous seront lancé par PHPUnit, donc pas d'injection de dépendance Auto du FW
        $omdbApiService = static::getContainer()->get(OmdbApi::class);
        // ne pas oublier le use sinon:  You have requested a non-existent service "App\Tests\Service\OmdbApi"

        // on utilise notre service, on récupère le résultat
        $result = $omdbApiService->fetch('Interstellar');

        //$this->assertTrue(is_array($result));
        $this->assertIsArray($result);

        // dd($result);
        /*
            ^ array:25 [
            "Title" => "Interstellar"
            "Year" => "2014"
            "Rated" => "PG-13"
            "Released" => "07 Nov 2014"
            "Runtime" => "169 min"
            "Genre" => "Adventure, Drama, Sci-Fi"
            "Director" => "Christopher Nolan"
            "Writer" => "Jonathan Nolan, Christopher Nolan"
            "Actors" => "Matthew McConaughey, Anne Hathaway, Jessica Chastain"
            "Plot" => "A team of explorers travel through a wormhole in space in an attempt to ensure humanity's survival."
            "Language" => "English"
            "Country" => "United States, United Kingdom, Canada"
            "Awards" => "Won 1 Oscar. 44 wins & 148 nominations total"
            "Poster" => "https://m.media-amazon.com/images/M/MV5BZjdkOTU3MDktN2IxOS00OGEyLWFmMjktY2FiMmZkNWIyODZiXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_SX300.jpg"
            "Ratings" => array:3 [
                0 => array:2 [
                "Source" => "Internet Movie Database"
                "Value" => "8.6/10"
                ]
                1 => array:2 [
                "Source" => "Rotten Tomatoes"
                "Value" => "72%"
                ]
                2 => array:2 [
                "Source" => "Metacritic"
                "Value" => "74/100"
                ]
            ]
            "Metascore" => "74"
            "imdbRating" => "8.6"
            "imdbVotes" => "1,676,203"
            "imdbID" => "tt0816692"
            "Type" => "movie"
            "DVD" => "31 Mar 2015"
            "BoxOffice" => "$188,020,017"
            "Production" => "N/A"
            "Website" => "N/A"
            "Response" => "True"
            ]
        */

        // ceci fait une erreur car la clé 'title' n'existe pas
        // $this->assertSame('Interstellar', $result['title']);
        // je vérifie donc que la clé existe, avec un T majuscule
        $this->assertArrayHasKey('Title', $result);

        // je teste la valeur du tableau àavec la clé 'Title'
        // 'Interstellar' === $result['Title'] ???
        $this->assertSame('Interstellar', $result['Title']);
    }

    public function testNotGood(): void
    {
        // (1) on démarre le FW Symfony
        $kernel = self::bootKernel();

        // assertSame( valeurAttendue, valeurATester)
        // @link https://phpunit.readthedocs.io/fr/latest/assertions.html
        // $this->assertSame('test', $kernel->getEnvironment());

        // avec la méthode statique getContainer()->get(FQCN_Class)
        // on récupère notre Service comme si on l'avait reçut par injection de dépendance
        // on doit faire comme ça parce que nous seront lancé par PHPUnit, donc pas d'injection de dépendance Auto du FW
        $omdbApiService = static::getContainer()->get(OmdbApi::class);
        // ne pas oublier le use sinon:  You have requested a non-existent service "App\Tests\Service\OmdbApi"

        // on utilise notre service, on récupère le résultat
        $result = $omdbApiService->fetch('Nain Porte Koi');

        //$this->assertTrue(is_array($result));
        $this->assertIsArray($result);

        // ceci fait une erreur car la clé 'title' n'existe pas
        // $this->assertSame('Interstellar', $result['title']);
        // je vérifie donc que la clé existe, avec un T majuscule
        $this->assertArrayNotHasKey('Title', $result);
        
        $this->assertArrayHasKey('Error', $result);
        
        $this->assertSame('Movie not found!', $result['Error']);        
    }
}
