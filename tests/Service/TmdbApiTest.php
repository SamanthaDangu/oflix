<?php

namespace App\Tests\Service;

use App\Service\TmdbApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TmdbApiTest extends TestCase
{
    public function testFetchListReturnsResults(): void
    {
        $tmdbApi = new TmdbApi(
            new MockHttpClient(new MockResponse(json_encode(['results' => [['id' => 550, 'title' => 'Fight Club']]], JSON_THROW_ON_ERROR))),
            new ParameterBag(['app.tmdb_api_key' => 'test']),
            new NullLogger()
        );

        $result = $tmdbApi->fetchList('movie', 'popular', 1);

        $this->assertIsArray($result);
        $this->assertSame(550, $result[0]['id']);
    }

    public function testFetchDetailsReturnsFullPayload(): void
    {
        $tmdbApi = new TmdbApi(
            new MockHttpClient(new MockResponse(json_encode(['id' => 550, 'title' => 'Fight Club', 'credits' => ['cast' => []]]))),
            new ParameterBag(['app.tmdb_api_key' => 'test']),
            new NullLogger()
        );

        $result = $tmdbApi->fetchDetails('movie', 550);

        $this->assertIsArray($result);
        $this->assertSame('Fight Club', $result['title']);
    }

    public function testFetchListReturnsNullOnHttpError(): void
    {
        $tmdbApi = new TmdbApi(
            new MockHttpClient(new MockResponse(json_encode(['status_message' => 'Invalid API key']), ['http_code' => 401])),
            new ParameterBag(['app.tmdb_api_key' => 'invalid']),
            new NullLogger()
        );

        $result = $tmdbApi->fetchList('movie', 'popular', 1);

        $this->assertNull($result);
    }

    public function testBuildPosterUrl(): void
    {
        $tmdbApi = new TmdbApi(
            new MockHttpClient(),
            new ParameterBag(['app.tmdb_api_key' => 'test']),
            new NullLogger()
        );

        $this->assertSame('https://image.tmdb.org/t/p/w500/abc.jpg', $tmdbApi->buildPosterUrl('/abc.jpg'));
        $this->assertNull($tmdbApi->buildPosterUrl(null));
    }
}
