<?php
declare(strict_types=1);
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20260818150000 extends AbstractMigration
{
    public function getDescription():string{return 'Add patient-facing location directions to healthcare services';}
    public function up(Schema $schema):void{$this->addSql('ALTER TABLE healthcare_service ADD location_directions TEXT DEFAULT NULL');}
    public function down(Schema $schema):void{$this->addSql('ALTER TABLE healthcare_service DROP location_directions');}
}
