<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Corrige les slugs en doublon existants (ex: deux films "La vie est belle")
 * puis ajoute des index uniques sur movie.slug, genre.slug et genre.name
 * pour empecher que le probleme ne se reproduise.
 */
final class Version20260928140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deduplique les slugs existants et ajoute des contraintes uniques (movie.slug, genre.slug, genre.name)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE movie m
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY slug ORDER BY id) AS rn
                FROM movie
            ) ranked ON m.id = ranked.id
            SET m.slug = CONCAT(m.slug, '-', m.id)
            WHERE ranked.rn > 1
        SQL);

        $this->addSql(<<<'SQL'
            UPDATE genre g
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY slug ORDER BY id) AS rn
                FROM genre
            ) ranked ON g.id = ranked.id
            SET g.slug = CONCAT(g.slug, '-', g.id)
            WHERE ranked.rn > 1
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_movie_slug ON movie (slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_genre_name ON genre (name)');
        $this->addSql('CREATE UNIQUE INDEX uniq_genre_slug ON genre (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_movie_slug ON movie');
        $this->addSql('DROP INDEX uniq_genre_name ON genre');
        $this->addSql('DROP INDEX uniq_genre_slug ON genre');
    }
}
