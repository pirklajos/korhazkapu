<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tenant-scoped sites, buildings, floors, rooms, departments, services and contact points';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE building (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(50) DEFAULT NULL, institution_id UUID NOT NULL, site_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E16F61D410405986 ON building (institution_id)');
        $this->addSql('CREATE INDEX IDX_E16F61D4F6BD1646 ON building (site_id)');
        $this->addSql('CREATE TABLE contact_point (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, label VARCHAR(120) NOT NULL, type VARCHAR(20) NOT NULL, value VARCHAR(255) NOT NULL, availability TEXT DEFAULT NULL, institution_id UUID NOT NULL, department_id UUID DEFAULT NULL, service_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9A8FE32B10405986 ON contact_point (institution_id)');
        $this->addSql('CREATE INDEX IDX_9A8FE32BAE80F5DF ON contact_point (department_id)');
        $this->addSql('CREATE INDEX IDX_9A8FE32BED5CA9E6 ON contact_point (service_id)');
        $this->addSql('CREATE TABLE department (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(120) NOT NULL, summary TEXT DEFAULT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_CD1DE18A10405986 ON department (institution_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_department_slug_tenant ON department (institution_id, slug)');
        $this->addSql('CREATE TABLE floor (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(100) NOT NULL, level_number INT DEFAULT NULL, institution_id UUID NOT NULL, building_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_BE45D62E10405986 ON floor (institution_id)');
        $this->addSql('CREATE INDEX IDX_BE45D62E4D2A7E12 ON floor (building_id)');
        $this->addSql('CREATE TABLE healthcare_service (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(120) NOT NULL, summary TEXT DEFAULT NULL, institution_id UUID NOT NULL, department_id UUID DEFAULT NULL, site_id UUID DEFAULT NULL, building_id UUID DEFAULT NULL, floor_id UUID DEFAULT NULL, room_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FD32156710405986 ON healthcare_service (institution_id)');
        $this->addSql('CREATE INDEX IDX_FD321567AE80F5DF ON healthcare_service (department_id)');
        $this->addSql('CREATE INDEX IDX_FD321567F6BD1646 ON healthcare_service (site_id)');
        $this->addSql('CREATE INDEX IDX_FD3215674D2A7E12 ON healthcare_service (building_id)');
        $this->addSql('CREATE INDEX IDX_FD321567854679E2 ON healthcare_service (floor_id)');
        $this->addSql('CREATE INDEX IDX_FD32156754177093 ON healthcare_service (room_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_service_slug_tenant ON healthcare_service (institution_id, slug)');
        $this->addSql('CREATE TABLE room (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(100) NOT NULL, number VARCHAR(40) DEFAULT NULL, institution_id UUID NOT NULL, floor_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_729F519B10405986 ON room (institution_id)');
        $this->addSql('CREATE INDEX IDX_729F519B854679E2 ON room (floor_id)');
        $this->addSql('CREATE TABLE site (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(120) NOT NULL, address VARCHAR(255) NOT NULL, map_url VARCHAR(500) DEFAULT NULL, accessibility TEXT DEFAULT NULL, active BOOLEAN NOT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_694309E410405986 ON site (institution_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_site_slug_tenant ON site (institution_id, slug)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D410405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D4F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE contact_point ADD CONSTRAINT FK_9A8FE32B10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE contact_point ADD CONSTRAINT FK_9A8FE32BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE contact_point ADD CONSTRAINT FK_9A8FE32BED5CA9E6 FOREIGN KEY (service_id) REFERENCES healthcare_service (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE floor ADD CONSTRAINT FK_BE45D62E10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE floor ADD CONSTRAINT FK_BE45D62E4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD32156710405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD321567AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD321567F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD3215674D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD321567854679E2 FOREIGN KEY (floor_id) REFERENCES floor (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE healthcare_service ADD CONSTRAINT FK_FD32156754177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B854679E2 FOREIGN KEY (floor_id) REFERENCES floor (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E410405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE building DROP CONSTRAINT FK_E16F61D410405986');
        $this->addSql('ALTER TABLE building DROP CONSTRAINT FK_E16F61D4F6BD1646');
        $this->addSql('ALTER TABLE contact_point DROP CONSTRAINT FK_9A8FE32B10405986');
        $this->addSql('ALTER TABLE contact_point DROP CONSTRAINT FK_9A8FE32BAE80F5DF');
        $this->addSql('ALTER TABLE contact_point DROP CONSTRAINT FK_9A8FE32BED5CA9E6');
        $this->addSql('ALTER TABLE department DROP CONSTRAINT FK_CD1DE18A10405986');
        $this->addSql('ALTER TABLE floor DROP CONSTRAINT FK_BE45D62E10405986');
        $this->addSql('ALTER TABLE floor DROP CONSTRAINT FK_BE45D62E4D2A7E12');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD32156710405986');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD321567AE80F5DF');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD321567F6BD1646');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD3215674D2A7E12');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD321567854679E2');
        $this->addSql('ALTER TABLE healthcare_service DROP CONSTRAINT FK_FD32156754177093');
        $this->addSql('ALTER TABLE room DROP CONSTRAINT FK_729F519B10405986');
        $this->addSql('ALTER TABLE room DROP CONSTRAINT FK_729F519B854679E2');
        $this->addSql('ALTER TABLE site DROP CONSTRAINT FK_694309E410405986');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE contact_point');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE floor');
        $this->addSql('DROP TABLE healthcare_service');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE site');
    }
}
