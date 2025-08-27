<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827152437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activations (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, priority VARCHAR(50) NOT NULL, channels JSON NOT NULL, target_audience VARCHAR(255) DEFAULT NULL, scheduled_for TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, organization_id VARCHAR(36) NOT NULL, created_by VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE assignees (id VARCHAR(36) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, avatar VARCHAR(500) DEFAULT NULL, active BOOLEAN NOT NULL, role VARCHAR(100) NOT NULL, department VARCHAR(100) NOT NULL, organization_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12C41EDCE7927C74 ON assignees (email)');
        $this->addSql('CREATE TABLE landing_pages (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, html_content TEXT NOT NULL, is_published BOOLEAN NOT NULL, has_contact_form BOOLEAN NOT NULL, contact_form_config JSON DEFAULT NULL, organization_id VARCHAR(36) NOT NULL, created_by VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_925A4323989D9B62 ON landing_pages (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activations');
        $this->addSql('DROP TABLE assignees');
        $this->addSql('DROP TABLE landing_pages');
    }
}
