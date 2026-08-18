<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name: 'building')]
class Building extends AbstractTenantEntity
{
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private Site $site;
    #[ORM\Column(length:255)] private string $name;
    #[ORM\Column(length:50,nullable:true)] private ?string $code=null;
    public function __construct(Institution $institution, Site $site, string $name) { parent::__construct($institution);$this->assertSameTenant($site);$this->site=$site;$this->name=$name; }
    public function getSite(): Site{return $this->site;} public function setSite(Site $v):self{$this->assertSameTenant($v);$this->site=$v;return $this;} public function getName():string{return $this->name;} public function setName(string $v):self{$this->name=$v;return $this;}
    public function getCode():?string{return $this->code;} public function setCode(?string $v):self{$this->code=$v;return $this;}
}
