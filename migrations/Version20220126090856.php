<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220126090856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE casting ADD actor_id INT NOT NULL');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA5010DAF24A FOREIGN KEY (actor_id) REFERENCES actor (id)');
        $this->addSql('CREATE INDEX IDX_D11BBA5010DAF24A ON casting (actor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA5010DAF24A');
        $this->addSql('DROP INDEX IDX_D11BBA5010DAF24A ON casting');
        $this->addSql('ALTER TABLE casting DROP actor_id');
    }
}
