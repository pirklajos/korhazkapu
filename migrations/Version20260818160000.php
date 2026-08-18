<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20260818160000 extends AbstractMigration
{
    public function getDescription():string{return 'Add M5 search synonyms and anonymous page feedback';}
    public function up(Schema $schema):void
    {
        $this->addSql('CREATE TABLE feedback (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, page_path VARCHAR(500) NOT NULL, helpful BOOLEAN NOT NULL, comment TEXT DEFAULT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');$this->addSql('CREATE INDEX IDX_D229445810405986 ON feedback (institution_id)');
        $this->addSql('CREATE TABLE search_synonym (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, term VARCHAR(120) NOT NULL, synonyms JSON NOT NULL, institution_id UUID NOT NULL, PRIMARY KEY (id))');$this->addSql('CREATE INDEX IDX_2BE0B15210405986 ON search_synonym (institution_id)');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D229445810405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');$this->addSql('ALTER TABLE search_synonym ADD CONSTRAINT FK_2BE0B15210405986 FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE NOT DEFERRABLE');
    }
    public function down(Schema $schema):void{$this->addSql('ALTER TABLE feedback DROP CONSTRAINT FK_D229445810405986');$this->addSql('ALTER TABLE search_synonym DROP CONSTRAINT FK_2BE0B15210405986');$this->addSql('DROP TABLE feedback');$this->addSql('DROP TABLE search_synonym');}
}
