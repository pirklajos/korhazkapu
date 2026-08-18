<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'announcement')]
class Announcement extends AbstractContent
{
    #[ORM\Column(length:20)] private string $type='normal'; #[ORM\Column(type:Types::TEXT)] private string $body=''; #[ORM\Column] private int $priority=0;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Site $site=null; #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Department $department=null; #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Service $service=null;
    #[ORM\Column(length:120,nullable:true)] private ?string $actionLabel=null; #[ORM\Column(length:500,nullable:true)] private ?string $actionUrl=null;
    public function setType(string $v):self{$this->type=$v;return $this;} public function setBody(string $v):self{$this->body=$v;return $this;} public function setPriority(int $v):self{$this->priority=$v;return $this;}
    public function getType():string{return $this->type;} public function getBody():string{return $this->body;} public function getPriority():int{return $this->priority;}
}
