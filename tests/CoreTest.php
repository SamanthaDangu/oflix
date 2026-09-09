<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class CoreTest extends WebTestCase
{
    protected function setUp(): void
    {
        $databaseUrl = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: '';

        if (str_contains($databaseUrl, 'db_user') || str_contains($databaseUrl, 'db_password') || str_contains($databaseUrl, 'db_name')) {
            $this->markTestSkipped('Base de test Doctrine non configuree : DATABASE_URL contient encore les valeurs placeholder.');
        }
    }
}
