<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827202551 extends AbstractMigration
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
        $this->addSql('CREATE TABLE assignment_rules (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, active BOOLEAN NOT NULL, priority INT NOT NULL, conditions JSON NOT NULL, assignment_type VARCHAR(50) NOT NULL, assignee_ids JSON NOT NULL, organization_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE landing_pages (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, html_content TEXT NOT NULL, is_published BOOLEAN NOT NULL, has_contact_form BOOLEAN NOT NULL, contact_form_config JSON DEFAULT NULL, variables JSON NOT NULL, organization_id VARCHAR(36) NOT NULL, created_by VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_925A4323989D9B62 ON landing_pages (slug)');
        $this->addSql('CREATE TABLE potential_customer (id VARCHAR(36) NOT NULL, type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, first_name VARCHAR(80) DEFAULT NULL, last_name VARCHAR(80) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(32) DEFAULT NULL, address TEXT DEFAULT NULL, city VARCHAR(64) DEFAULT NULL, country VARCHAR(64) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, industry VARCHAR(100) DEFAULT NULL, tags JSON NOT NULL, priority VARCHAR(10) NOT NULL, assigned_to VARCHAR(36) DEFAULT NULL, assigned_to_name VARCHAR(255) DEFAULT NULL, total_value NUMERIC(10, 2) NOT NULL, potential_value NUMERIC(10, 2) NOT NULL, last_contact_date VARCHAR(10) DEFAULT NULL, requests_count INT NOT NULL, quotations_count INT NOT NULL, conversations_count INT NOT NULL, organization_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, converted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE potential_customer_email (id UUID NOT NULL, value VARCHAR(255) NOT NULL, registered_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, potential_customer_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_DCBDAA8D96798203 ON potential_customer_email (potential_customer_id)');
        $this->addSql('CREATE TABLE quotation (id UUID NOT NULL, details JSON NOT NULL, status VARCHAR(16) NOT NULL, organization_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, request_information_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_474A8DB975A68C6C ON quotation (request_information_id)');
        $this->addSql('CREATE TABLE request_information (id UUID NOT NULL, organization_id VARCHAR(36) NOT NULL, program_interest_id VARCHAR(36) NOT NULL, lead_origin_id VARCHAR(36) NOT NULL, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(32) NOT NULL, city VARCHAR(64) NOT NULL, assignee_id VARCHAR(36) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_user_updated JSON DEFAULT NULL, status_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_285BBD66BF700BD ON request_information (status_id)');
        $this->addSql('CREATE INDEX idx_program_interest_id ON request_information (program_interest_id)');
        $this->addSql('CREATE INDEX idx_lead_origin_id ON request_information (lead_origin_id)');
        $this->addSql('CREATE INDEX idx_organization_id ON request_information (organization_id)');
        $this->addSql('CREATE TABLE request_information_status (id UUID NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, organization_id VARCHAR(36) NOT NULL, sort INT DEFAULT 0 NOT NULL, is_default BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E922A03F77153098 ON request_information_status (code)');
        $this->addSql('CREATE UNIQUE INDEX uniq_code_organization ON request_information_status (code, organization_id)');
        $this->addSql('CREATE TABLE request_note (id UUID NOT NULL, text VARCHAR(512) NOT NULL, created_by VARCHAR(80) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, request_information_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B01ADDEE75A68C6C ON request_note (request_information_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('ALTER TABLE potential_customer_email ADD CONSTRAINT FK_DCBDAA8D96798203 FOREIGN KEY (potential_customer_id) REFERENCES potential_customer (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB975A68C6C FOREIGN KEY (request_information_id) REFERENCES request_information (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE request_information ADD CONSTRAINT FK_285BBD66BF700BD FOREIGN KEY (status_id) REFERENCES request_information_status (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE request_note ADD CONSTRAINT FK_B01ADDEE75A68C6C FOREIGN KEY (request_information_id) REFERENCES request_information (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE potential_customer_email DROP CONSTRAINT FK_DCBDAA8D96798203');
        $this->addSql('ALTER TABLE quotation DROP CONSTRAINT FK_474A8DB975A68C6C');
        $this->addSql('ALTER TABLE request_information DROP CONSTRAINT FK_285BBD66BF700BD');
        $this->addSql('ALTER TABLE request_note DROP CONSTRAINT FK_B01ADDEE75A68C6C');
        $this->addSql('DROP TABLE activations');
        $this->addSql('DROP TABLE assignees');
        $this->addSql('DROP TABLE assignment_rules');
        $this->addSql('DROP TABLE landing_pages');
        $this->addSql('DROP TABLE potential_customer');
        $this->addSql('DROP TABLE potential_customer_email');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('DROP TABLE request_information');
        $this->addSql('DROP TABLE request_information_status');
        $this->addSql('DROP TABLE request_note');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
