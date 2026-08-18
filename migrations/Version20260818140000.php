<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818140000 extends AbstractMigration
{
    public function getDescription():string{return 'Add content revisions and tenant audit log for M3 workflow';}
    public function up(Schema $schema):void
    {
        $this->addSql('CREATE TABLE audit_log (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(80) NOT NULL, subject_type VARCHAR(120) NOT NULL, subject_id VARCHAR(36) NOT NULL, details JSON NOT NULL, institution_id UUID NOT NULL, actor_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F6E1C0F510405986 ON audit_log (institution_id)');$this->addSql('CREATE INDEX IDX_F6E1C0F510DAF24A ON audit_log (actor_id)');
        $this->addSql('CREATE TABLE content_revision (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content_type VARCHAR(120) NOT NULL, content_id VARCHAR(36) NOT NULL, version_number INT NOT NULL, snapshot JSON NOT NULL, summary TEXT DEFAULT NULL, institution_id UUID NOT NULL, author_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3FD4FF2310405986 ON content_revision (institution_id)');$this->addSql('CREATE INDEX IDX_3FD4FF23F675F31B ON content_revision (author_id)');$this->addSql('CREATE UNIQUE INDEX UNIQ_CONTENT_REVISION_VERSION ON content_revision (content_type, content_id, version_number)');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F510405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');$this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F510DAF24A FOREIGN KEY (actor_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE content_revision ADD CONSTRAINT FK_3FD4FF2310405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');$this->addSql('ALTER TABLE content_revision ADD CONSTRAINT FK_3FD4FF23F675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');
    }
    public function down(Schema $schema):void
    {
        $this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_F6E1C0F510405986');$this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_F6E1C0F510DAF24A');$this->addSql('ALTER TABLE content_revision DROP CONSTRAINT FK_3FD4FF2310405986');$this->addSql('ALTER TABLE content_revision DROP CONSTRAINT FK_3FD4FF23F675F31B');$this->addSql('DROP TABLE audit_log');$this->addSql('DROP TABLE content_revision');
    }
}
