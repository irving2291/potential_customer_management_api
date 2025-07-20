<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718222522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_information DROP country, DROP state, CHANGE program_interest_id program_interest_id VARCHAR(36) NOT NULL, CHANGE lead_origin_id lead_origin_id VARCHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_information ADD country VARCHAR(64) NOT NULL, ADD state VARCHAR(64) NOT NULL, CHANGE program_interest_id program_interest_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE lead_origin_id lead_origin_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
