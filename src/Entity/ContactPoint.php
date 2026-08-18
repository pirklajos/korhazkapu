<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'contact_point')]
class ContactPoint extends AbstractTenantEntity
{
    #[ORM\Column(length:120)] private string $label; #[ORM\Column(length:20)] private string $type; #[ORM\Column(length:255)] private string $value;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $availability=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Department $department=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Service $service=null;
    public function __construct(Institution $institution,string $label,string $type,string $value){parent::__construct($institution);$this->label=$label;$this->type=$type;$this->value=$value;}
    public function getLabel():string{return $this->label;} public function getType():string{return $this->type;} public function getValue():string{return $this->value;}
    public function getAvailability():?string{return $this->availability;} public function setAvailability(?string $v):self{$this->availability=$v;return $this;}
    public function setDepartment(?Department $v):self{if($v)$this->assertSameTenant($v);$this->department=$v;return $this;}
    public function setService(?Service $v):self{if($v)$this->assertSameTenant($v);$this->service=$v;return $this;}
}
