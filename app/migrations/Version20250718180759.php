<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718180759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_information ADD program_interest_id VARCHAR(36) NOT NULL, ADD lead_origin_id VARCHAR(36) NOT NULL, ADD first_name VARCHAR(80) NOT NULL, ADD last_name VARCHAR(80) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD phone VARCHAR(32) NOT NULL, ADD country VARCHAR(64) NOT NULL, ADD state VARCHAR(64) NOT NULL, ADD city VARCHAR(64) NOT NULL, DROP customer_name, DROP program_interest, DROP lead_origin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_information ADD program_interest VARCHAR(255) NOT NULL, ADD lead_origin VARCHAR(128) NOT NULL, DROP program_interest_id, DROP lead_origin_id, DROP first_name, DROP last_name, DROP phone, DROP country, DROP state, DROP city, CHANGE email customer_name VARCHAR(255) NOT NULL');
    }
}
