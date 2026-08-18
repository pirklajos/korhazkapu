<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'healthcare_service')] #[ORM\UniqueConstraint(name:'uniq_service_slug_tenant',columns:['institution_id','slug'])]
class Service extends AbstractTenantEntity
{
    #[ORM\Column(length:255)] private string $name; #[ORM\Column(length:120)] private string $slug;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $summary=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Department $department=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Site $site=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Building $building=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Floor $floor=null;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Room $room=null;
    public function __construct(Institution $institution,string $name,string $slug){parent::__construct($institution);$this->name=$name;$this->slug=$slug;}
    public function getName():string{return $this->name;} public function setName(string $v):self{$this->name=$v;return $this;} public function getSlug():string{return $this->slug;}
    public function setSlug(string $v):self{$this->slug=$v;return $this;}
    public function getSummary():?string{return $this->summary;} public function setSummary(?string $v):self{$this->summary=$v;return $this;}
    public function getDepartment():?Department{return $this->department;} public function setDepartment(?Department $v):self{if($v)$this->assertSameTenant($v);$this->department=$v;return $this;}
    public function getSite():?Site{return $this->site;} public function setSite(?Site $v):self{if($v)$this->assertSameTenant($v);$this->site=$v;return $this;}
    public function getBuilding():?Building{return $this->building;} public function setBuilding(?Building $v):self{if($v)$this->assertSameTenant($v);$this->building=$v;return $this;}
    public function getFloor():?Floor{return $this->floor;} public function setFloor(?Floor $v):self{if($v)$this->assertSameTenant($v);$this->floor=$v;return $this;}
    public function getRoom():?Room{return $this->room;} public function setRoom(?Room $v):self{if($v)$this->assertSameTenant($v);$this->room=$v;return $this;}
}
