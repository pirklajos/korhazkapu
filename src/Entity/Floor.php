<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'floor')]
class Floor extends AbstractTenantEntity
{
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private Building $building;
    #[ORM\Column(length:100)] private string $name; #[ORM\Column(nullable:true)] private ?int $levelNumber=null;
    public function __construct(Institution $institution, Building $building, string $name){parent::__construct($institution);$this->assertSameTenant($building);$this->building=$building;$this->name=$name;}
    public function getBuilding():Building{return $this->building;} public function setBuilding(Building $v):self{$this->assertSameTenant($v);$this->building=$v;return $this;} public function getName():string{return $this->name;} public function setName(string $v):self{$this->name=$v;return $this;}
    public function getLevelNumber():?int{return $this->levelNumber;} public function setLevelNumber(?int $v):self{$this->levelNumber=$v;return $this;}
}
