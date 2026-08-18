<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818090000 extends AbstractMigration
{
    public function getDescription(): string { return 'Create M1 institution, theme, user and membership model'; }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE institution (id UUID NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(80) NOT NULL, slug VARCHAR(120) NOT NULL, status VARCHAR(20) NOT NULL, primary_domain VARCHAR(255) DEFAULT NULL, domains JSON NOT NULL, central_address VARCHAR(255) NOT NULL, phone VARCHAR(60) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, maintainer_name VARCHAR(255) DEFAULT NULL, default_locale VARCHAR(10) NOT NULL, timezone VARCHAR(64) NOT NULL, privacy_url VARCHAR(500) DEFAULT NULL, legal_url VARCHAR(500) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_institution_slug ON institution (slug)');
        $this->addSql('CREATE UNIQUE INDEX uniq_institution_primary_domain ON institution (primary_domain)');
        $this->addSql('CREATE TABLE institution_theme (id UUID NOT NULL, institution_id UUID NOT NULL, primary_color VARCHAR(7) NOT NULL, secondary_color VARCHAR(7) NOT NULL, accent_color VARCHAR(7) NOT NULL, text_color VARCHAR(7) NOT NULL, background_color VARCHAR(7) NOT NULL, danger_color VARCHAR(7) NOT NULL, warning_color VARCHAR(7) NOT NULL, success_color VARCHAR(7) NOT NULL, light_logo_path VARCHAR(500) DEFAULT NULL, dark_logo_path VARCHAR(500) DEFAULT NULL, favicon_path VARCHAR(500) DEFAULT NULL, hero_image_path VARCHAR(500) DEFAULT NULL, font_family VARCHAR(40) NOT NULL, header_variant VARCHAR(40) NOT NULL, corner_radius VARCHAR(20) NOT NULL, component_style VARCHAR(30) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_THEME_INSTITUTION ON institution_theme (institution_id)');
        $this->addSql('CREATE TABLE app_user (id UUID NOT NULL, email VARCHAR(180) NOT NULL, display_name VARCHAR(160) NOT NULL, password VARCHAR(255) NOT NULL, platform_roles JSON NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_EMAIL ON app_user (email)');
        $this->addSql('CREATE TABLE membership (id UUID NOT NULL, user_id UUID NOT NULL, institution_id UUID NOT NULL, roles JSON NOT NULL, site_ids JSON NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_MEMBERSHIP_USER ON membership (user_id)');
        $this->addSql('CREATE INDEX IDX_MEMBERSHIP_INSTITUTION ON membership (institution_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_membership_user_institution ON membership (user_id, institution_id)');
        $this->addSql('ALTER TABLE institution_theme ADD CONSTRAINT FK_THEME_INSTITUTION FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_MEMBERSHIP_USER FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_MEMBERSHIP_INSTITUTION FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE membership');
        $this->addSql('DROP TABLE institution_theme');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE institution');
    }
}
