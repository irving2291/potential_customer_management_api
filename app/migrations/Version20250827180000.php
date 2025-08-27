<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add assignment rules table and assignee_id column to request_information table';
    }

    public function up(Schema $schema): void
    {
        // Create assignment_rules table
        $this->addSql('CREATE TABLE assignment_rules (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            priority INT NOT NULL DEFAULT 1,
            conditions JSON NOT NULL,
            assignment_type VARCHAR(50) NOT NULL,
            assignee_ids JSON NOT NULL,
            organization_id VARCHAR(36) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            INDEX idx_organization_id (organization_id),
            INDEX idx_active_priority (active, priority),
            UNIQUE KEY unique_name_org (name, organization_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add assignee_id column to request_information table
        $this->addSql('ALTER TABLE request_information ADD assignee_id VARCHAR(36) DEFAULT NULL');
        
        // Add index for assignee_id for better query performance
        $this->addSql('CREATE INDEX idx_assignee_id ON request_information (assignee_id)');
        
        // Add composite index for assignee_id and organization_id
        $this->addSql('CREATE INDEX idx_assignee_organization ON request_information (assignee_id, organization_id)');
    }

    public function down(Schema $schema): void
    {
        // Remove indexes first
        $this->addSql('DROP INDEX idx_assignee_organization ON request_information');
        $this->addSql('DROP INDEX idx_assignee_id ON request_information');
        
        // Remove assignee_id column from request_information table
        $this->addSql('ALTER TABLE request_information DROP assignee_id');
        
        // Drop assignment_rules table
        $this->addSql('DROP TABLE assignment_rules');
    }
}