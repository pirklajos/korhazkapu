<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'information_page')] #[ORM\UniqueConstraint(name:'uniq_info_slug_tenant',columns:['institution_id','slug'])]
class InformationPage extends AbstractContent
{
    /** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $content=[];
    #[ORM\Column(length:80,nullable:true)] private ?string $category=null;
    /** @var list<string> */ #[ORM\Column(type:Types::JSON)] private array $tags=[];
    #[ORM\Column(length:255,nullable:true)] private ?string $medicalOwner=null;
    #[ORM\Column(length:255,nullable:true)] private ?string $seoTitle=null;
    #[ORM\Column(length:500,nullable:true)] private ?string $seoDescription=null;
    /** @param array<string,mixed> $v */ public function setContent(array $v):self{$this->content=$v;return $this;} /** @return array<string,mixed> */ public function getContent():array{return $this->content;}
    /** @param list<string> $v */ public function setTags(array $v):self{$this->tags=array_values(array_unique($v));return $this;} /** @return list<string> */ public function getTags():array{return $this->tags;}
    public function setCategory(?string $v):self{$this->category=$v;return $this;}
}
