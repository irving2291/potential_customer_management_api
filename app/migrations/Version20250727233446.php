<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727233446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE potential_customer (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, phone VARCHAR(32) DEFAULT NULL, city VARCHAR(64) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE potential_customer_email (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', potential_customer_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', value VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DCBDAA8D96798203 (potential_customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quotation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', request_information_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', details JSON NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_474A8DB975A68C6C (request_information_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_information (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', status_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', program_interest_id VARCHAR(36) NOT NULL, lead_origin_id VARCHAR(36) NOT NULL, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(32) NOT NULL, city VARCHAR(64) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, last_user_updated JSON DEFAULT NULL, INDEX IDX_285BBD66BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_information_status (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_E922A03F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_note (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', request_information_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', text VARCHAR(512) NOT NULL, created_by VARCHAR(80) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B01ADDEE75A68C6C (request_information_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE potential_customer_email ADD CONSTRAINT FK_DCBDAA8D96798203 FOREIGN KEY (potential_customer_id) REFERENCES potential_customer (id)');
        $this->addSql('ALTER TABLE quotation ADD CONSTRAINT FK_474A8DB975A68C6C FOREIGN KEY (request_information_id) REFERENCES request_information (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE request_information ADD CONSTRAINT FK_285BBD66BF700BD FOREIGN KEY (status_id) REFERENCES request_information_status (id)');
        $this->addSql('ALTER TABLE request_note ADD CONSTRAINT FK_B01ADDEE75A68C6C FOREIGN KEY (request_information_id) REFERENCES request_information (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE potential_customer_email DROP FOREIGN KEY FK_DCBDAA8D96798203');
        $this->addSql('ALTER TABLE quotation DROP FOREIGN KEY FK_474A8DB975A68C6C');
        $this->addSql('ALTER TABLE request_information DROP FOREIGN KEY FK_285BBD66BF700BD');
        $this->addSql('ALTER TABLE request_note DROP FOREIGN KEY FK_B01ADDEE75A68C6C');
        $this->addSql('DROP TABLE potential_customer');
        $this->addSql('DROP TABLE potential_customer_email');
        $this->addSql('DROP TABLE quotation');
        $this->addSql('DROP TABLE request_information');
        $this->addSql('DROP TABLE request_information_status');
        $this->addSql('DROP TABLE request_note');
    }
}
