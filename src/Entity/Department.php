<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'department')] #[ORM\UniqueConstraint(name:'uniq_department_slug_tenant',columns:['institution_id','slug'])]
class Department extends AbstractTenantEntity
{
    #[ORM\Column(length:255)] private string $name; #[ORM\Column(length:120)] private string $slug;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $summary=null;
    public function __construct(Institution $institution,string $name,string $slug){parent::__construct($institution);$this->name=$name;$this->slug=$slug;}
    public function getName():string{return $this->name;} public function setName(string $v):self{$this->name=$v;return $this;} public function getSlug():string{return $this->slug;}
    public function getSummary():?string{return $this->summary;} public function setSummary(?string $v):self{$this->summary=$v;return $this;}
}
