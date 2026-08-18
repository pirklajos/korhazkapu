<?php
declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260818130000 extends AbstractMigration
{
    public function getDescription():string{return 'Add structured building, floor and room location to patient journey steps';}
    public function up(Schema $schema):void
    {
        $this->addSql('ALTER TABLE journey_step ADD building_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE journey_step ADD floor_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE journey_step ADD room_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE journey_step ADD CONSTRAINT FK_5CD5718F4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE journey_step ADD CONSTRAINT FK_5CD5718F854679E2 FOREIGN KEY (floor_id) REFERENCES floor (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE journey_step ADD CONSTRAINT FK_5CD5718F54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_5CD5718F4D2A7E12 ON journey_step (building_id)');
        $this->addSql('CREATE INDEX IDX_5CD5718F854679E2 ON journey_step (floor_id)');
        $this->addSql('CREATE INDEX IDX_5CD5718F54177093 ON journey_step (room_id)');
    }
    public function down(Schema $schema):void
    {
        $this->addSql('ALTER TABLE journey_step DROP CONSTRAINT FK_5CD5718F4D2A7E12');
        $this->addSql('ALTER TABLE journey_step DROP CONSTRAINT FK_5CD5718F854679E2');
        $this->addSql('ALTER TABLE journey_step DROP CONSTRAINT FK_5CD5718F54177093');
        $this->addSql('DROP INDEX IDX_5CD5718F4D2A7E12');
        $this->addSql('DROP INDEX IDX_5CD5718F854679E2');
        $this->addSql('DROP INDEX IDX_5CD5718F54177093');
        $this->addSql('ALTER TABLE journey_step DROP building_id');
        $this->addSql('ALTER TABLE journey_step DROP floor_id');
        $this->addSql('ALTER TABLE journey_step DROP room_id');
    }
}
