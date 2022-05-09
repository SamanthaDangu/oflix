<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220131082240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD username VARCHAR(50) NOT NULL, ADD reactions LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', ADD watched_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE movie_id movie_id INT DEFAULT NULL, CHANGE title email VARCHAR(255) NOT NULL, CHANGE description content LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP username, DROP reactions, DROP watched_at, CHANGE movie_id movie_id INT NOT NULL, CHANGE email title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
