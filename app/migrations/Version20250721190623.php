<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721190623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request_note (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', request_information_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', text VARCHAR(512) NOT NULL, created_by VARCHAR(80) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B01ADDEE75A68C6C (request_information_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request_note ADD CONSTRAINT FK_B01ADDEE75A68C6C FOREIGN KEY (request_information_id) REFERENCES request_information (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_note DROP FOREIGN KEY FK_B01ADDEE75A68C6C');
        $this->addSql('DROP TABLE request_note');
    }
}
