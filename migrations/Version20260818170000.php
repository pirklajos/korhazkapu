<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add global content templates and tenant template adoption tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE content_template (id UUID NOT NULL, template_key VARCHAR(120) NOT NULL, title VARCHAR(255) NOT NULL, summary TEXT DEFAULT NULL, content JSON NOT NULL, version INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C5D7F56C24FB228B ON content_template (template_key)');
        $this->addSql('CREATE TABLE template_adoption (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, adopted_version INT NOT NULL, institution_id UUID NOT NULL, template_id UUID NOT NULL, page_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B28886DA10405986 ON template_adoption (institution_id)');
        $this->addSql('CREATE INDEX IDX_B28886DA5DA0FB8 ON template_adoption (template_id)');
        $this->addSql('CREATE INDEX IDX_B28886DAC4663E4 ON template_adoption (page_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_template_adoption ON template_adoption (institution_id, template_id)');
        $this->addSql('ALTER TABLE template_adoption ADD CONSTRAINT FK_B28886DA10405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE template_adoption ADD CONSTRAINT FK_B28886DA5DA0FB8 FOREIGN KEY (template_id) REFERENCES content_template (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE template_adoption ADD CONSTRAINT FK_B28886DAC4663E4 FOREIGN KEY (page_id) REFERENCES information_page (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE template_adoption DROP CONSTRAINT FK_B28886DA10405986');
        $this->addSql('ALTER TABLE template_adoption DROP CONSTRAINT FK_B28886DA5DA0FB8');
        $this->addSql('ALTER TABLE template_adoption DROP CONSTRAINT FK_B28886DAC4663E4');
        $this->addSql('DROP TABLE template_adoption');
        $this->addSql('DROP TABLE content_template');
    }
}
