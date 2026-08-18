<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260818121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create M2 information pages, procedure guides, patient journeys, announcements and media assets';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announcement (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(140) NOT NULL, summary TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, review_due_at DATE DEFAULT NULL, type VARCHAR(20) NOT NULL, body TEXT NOT NULL, priority INT NOT NULL, action_label VARCHAR(120) DEFAULT NULL, action_url VARCHAR(500) DEFAULT NULL, institution_id UUID NOT NULL, site_id UUID DEFAULT NULL, department_id UUID DEFAULT NULL, service_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4DB9D91C10405986 ON announcement (institution_id)');
        $this->addSql('CREATE INDEX IDX_4DB9D91CF6BD1646 ON announcement (site_id)');
        $this->addSql('CREATE INDEX IDX_4DB9D91CAE80F5DF ON announcement (department_id)');
        $this->addSql('CREATE INDEX IDX_4DB9D91CED5CA9E6 ON announcement (service_id)');
        $this->addSql('CREATE TABLE information_page (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(140) NOT NULL, summary TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, review_due_at DATE DEFAULT NULL, content JSON NOT NULL, category VARCHAR(80) DEFAULT NULL, tags JSON NOT NULL, medical_owner VARCHAR(255) DEFAULT NULL, seo_title VARCHAR(255) DEFAULT NULL, seo_description VARCHAR(500) DEFAULT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_35A2BF3D10405986 ON information_page (institution_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_info_slug_tenant ON information_page (institution_id, slug)');
        $this->addSql('CREATE TABLE journey_step (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, position INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, location VARCHAR(255) DEFAULT NULL, action TEXT DEFAULT NULL, required_documents JSON NOT NULL, institution_id UUID NOT NULL, journey_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5CD5718F10405986 ON journey_step (institution_id)');
        $this->addSql('CREATE INDEX IDX_5CD5718FD5C9896F ON journey_step (journey_id)');
        $this->addSql('CREATE TABLE media_asset (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, original_name VARCHAR(255) NOT NULL, storage_path VARCHAR(500) NOT NULL, mime_type VARCHAR(120) NOT NULL, size_bytes INT NOT NULL, alt_text VARCHAR(500) NOT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1DB69EED10405986 ON media_asset (institution_id)');
        $this->addSql('CREATE TABLE patient_journey (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(140) NOT NULL, summary TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, review_due_at DATE DEFAULT NULL, target_audience VARCHAR(255) DEFAULT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9575934910405986 ON patient_journey (institution_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_journey_slug_tenant ON patient_journey (institution_id, slug)');
        $this->addSql('CREATE TABLE procedure_guide (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(140) NOT NULL, summary TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, review_due_at DATE DEFAULT NULL, duration_minutes INT DEFAULT NULL, purpose TEXT DEFAULT NULL, discomfort TEXT DEFAULT NULL, preparation JSON NOT NULL, required_documents JSON NOT NULL, referral_required BOOLEAN NOT NULL, appointment_required BOOLEAN NOT NULL, companion_required BOOLEAN NOT NULL, can_drive_after BOOLEAN DEFAULT NULL, aftercare JSON NOT NULL, institution_id UUID NOT NULL, service_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_29E5787F10405986 ON procedure_guide (institution_id)');
        $this->addSql('CREATE INDEX IDX_29E5787FED5CA9E6 ON procedure_guide (service_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_guide_slug_tenant ON procedure_guide (institution_id, slug)');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91C10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CED5CA9E6 FOREIGN KEY (service_id) REFERENCES healthcare_service (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE information_page ADD CONSTRAINT FK_35A2BF3D10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE journey_step ADD CONSTRAINT FK_5CD5718F10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE journey_step ADD CONSTRAINT FK_5CD5718FD5C9896F FOREIGN KEY (journey_id) REFERENCES patient_journey (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE media_asset ADD CONSTRAINT FK_1DB69EED10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE patient_journey ADD CONSTRAINT FK_9575934910405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE procedure_guide ADD CONSTRAINT FK_29E5787F10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE procedure_guide ADD CONSTRAINT FK_29E5787FED5CA9E6 FOREIGN KEY (service_id) REFERENCES healthcare_service (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91C10405986');
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91CF6BD1646');
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91CAE80F5DF');
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91CED5CA9E6');
        $this->addSql('ALTER TABLE information_page DROP CONSTRAINT FK_35A2BF3D10405986');
        $this->addSql('ALTER TABLE journey_step DROP CONSTRAINT FK_5CD5718F10405986');
        $this->addSql('ALTER TABLE journey_step DROP CONSTRAINT FK_5CD5718FD5C9896F');
        $this->addSql('ALTER TABLE media_asset DROP CONSTRAINT FK_1DB69EED10405986');
        $this->addSql('ALTER TABLE patient_journey DROP CONSTRAINT FK_9575934910405986');
        $this->addSql('ALTER TABLE procedure_guide DROP CONSTRAINT FK_29E5787F10405986');
        $this->addSql('ALTER TABLE procedure_guide DROP CONSTRAINT FK_29E5787FED5CA9E6');
        $this->addSql('DROP TABLE announcement');
        $this->addSql('DROP TABLE information_page');
        $this->addSql('DROP TABLE journey_step');
        $this->addSql('DROP TABLE media_asset');
        $this->addSql('DROP TABLE patient_journey');
        $this->addSql('DROP TABLE procedure_guide');
    }
}
