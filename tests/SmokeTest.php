<?php

namespace App\Tests;

use App\Entity\Casting;
use App\Entity\Movie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SmokeTest extends CoreTest
{
    public function testBackOfficePagesRenderForAdmin(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $admin = $em->getRepository(User::class)->findOneBy(['email' => 'admin@admin.com']);
        $this->assertNotNull($admin, 'fixtures doivent etre chargees (admin@admin.com)');
        $client->loginUser($admin);

        $movie = $em->getRepository(Movie::class)->findOneBy([]);
        $casting = $em->getRepository(Casting::class)->findOneBy([]);

        $urls = [
            '/back/movie/',
            '/back/movie/new',
            '/back/movie/' . $movie->getId(),
            '/back/movie/' . $movie->getId() . '/edit',
            '/back/user/',
            '/back/user/new',
            '/back/user/' . $admin->getId(),
            '/back/user/' . $admin->getId() . '/edit',
            '/back/casting/',
            '/back/casting/new',
            '/back/casting/' . $casting->getId(),
            '/back/casting/' . $casting->getId() . '/edit',
        ];

        foreach ($urls as $url) {
            $client->request('GET', $url);
            $this->assertResponseIsSuccessful($url);
        }
    }

    public function testFrontCataloguePagesRender(): void
    {
        $client = static::createClient();

        foreach (['/catalogue', '/films', '/series', '/genres'] as $url) {
            $client->request('GET', $url);
            $this->assertResponseIsSuccessful($url);
        }
    }

    public function testReviewFormUsesMovieSlugRoute(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $movie = $em->getRepository(Movie::class)->findOneBy([]);
        $client->loginUser($user);

        $client->request('GET', '/movie/' . $movie->getSlug() . '/review');
        $this->assertResponseIsSuccessful();
    }

    public function testFavoritesAndProfilePagesRenderWithSeededData(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $client->loginUser($user);

        foreach (['/favorites', '/profile'] as $url) {
            $client->request('GET', $url);
            $this->assertResponseIsSuccessful($url);
        }
    }
}
