<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827180241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignment_rules (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, active BOOLEAN NOT NULL, priority INT NOT NULL, conditions JSON NOT NULL, assignment_type VARCHAR(50) NOT NULL, assignee_ids JSON NOT NULL, organization_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE potential_customer ADD type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD company_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD country VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD website VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD industry VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD tags JSON NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD priority VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD assigned_to VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD assigned_to_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD total_value NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD potential_value NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD last_contact_date VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD requests_count INT NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD quotations_count INT NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD conversations_count INT NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD organization_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ADD converted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE potential_customer ALTER id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE potential_customer ALTER first_name DROP NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ALTER last_name DROP NOT NULL');
        $this->addSql('ALTER TABLE potential_customer_email ALTER potential_customer_id TYPE VARCHAR(36)');
        $this->addSql('ALTER TABLE request_information ADD assignee_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE assignment_rules');
        $this->addSql('ALTER TABLE potential_customer DROP type');
        $this->addSql('ALTER TABLE potential_customer DROP status');
        $this->addSql('ALTER TABLE potential_customer DROP company_name');
        $this->addSql('ALTER TABLE potential_customer DROP address');
        $this->addSql('ALTER TABLE potential_customer DROP country');
        $this->addSql('ALTER TABLE potential_customer DROP website');
        $this->addSql('ALTER TABLE potential_customer DROP industry');
        $this->addSql('ALTER TABLE potential_customer DROP tags');
        $this->addSql('ALTER TABLE potential_customer DROP priority');
        $this->addSql('ALTER TABLE potential_customer DROP assigned_to');
        $this->addSql('ALTER TABLE potential_customer DROP assigned_to_name');
        $this->addSql('ALTER TABLE potential_customer DROP total_value');
        $this->addSql('ALTER TABLE potential_customer DROP potential_value');
        $this->addSql('ALTER TABLE potential_customer DROP last_contact_date');
        $this->addSql('ALTER TABLE potential_customer DROP requests_count');
        $this->addSql('ALTER TABLE potential_customer DROP quotations_count');
        $this->addSql('ALTER TABLE potential_customer DROP conversations_count');
        $this->addSql('ALTER TABLE potential_customer DROP organization_id');
        $this->addSql('ALTER TABLE potential_customer DROP created_at');
        $this->addSql('ALTER TABLE potential_customer DROP updated_at');
        $this->addSql('ALTER TABLE potential_customer DROP converted_at');
        $this->addSql('ALTER TABLE potential_customer ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE potential_customer ALTER first_name SET NOT NULL');
        $this->addSql('ALTER TABLE potential_customer ALTER last_name SET NOT NULL');
        $this->addSql('ALTER TABLE potential_customer_email ALTER potential_customer_id TYPE UUID');
        $this->addSql('ALTER TABLE request_information DROP assignee_id');
    }
}
