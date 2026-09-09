<?php

namespace App\Tests\Front;

use App\Tests\CoreTest;

class SeoTest extends CoreTest
{
    public function testRobotsTxtIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/robots.txt');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Sitemap:', $client->getResponse()->getContent());
    }

    public function testSitemapIsValidXml(): void
    {
        $client = static::createClient();
        $client->request('GET', '/sitemap.xml');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<urlset', $client->getResponse()->getContent());
    }
}
