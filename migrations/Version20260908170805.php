<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908170805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor ADD tmdb_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_actor_tmdb_id ON actor (tmdb_id)');
        $this->addSql('ALTER TABLE movie ADD tmdb_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_movie_tmdb_id_type ON movie (tmdb_id, type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_actor_tmdb_id ON actor');
        $this->addSql('ALTER TABLE actor DROP tmdb_id');
        $this->addSql('DROP INDEX idx_movie_tmdb_id_type ON movie');
        $this->addSql('ALTER TABLE movie DROP tmdb_id');
    }
}
