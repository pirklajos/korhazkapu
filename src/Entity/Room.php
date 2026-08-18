<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'room')]
class Room extends AbstractTenantEntity
{
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private Floor $floor;
    #[ORM\Column(length:100)] private string $name; #[ORM\Column(length:40,nullable:true)] private ?string $number=null;
    public function __construct(Institution $institution, Floor $floor, string $name){parent::__construct($institution);$this->assertSameTenant($floor);$this->floor=$floor;$this->name=$name;}
    public function getFloor():Floor{return $this->floor;} public function getName():string{return $this->name;} public function setName(string $v):self{$this->name=$v;return $this;}
    public function getNumber():?string{return $this->number;} public function setNumber(?string $v):self{$this->number=$v;return $this;}
}
