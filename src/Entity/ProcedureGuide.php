<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'procedure_guide')] #[ORM\UniqueConstraint(name:'uniq_guide_slug_tenant',columns:['institution_id','slug'])]
class ProcedureGuide extends AbstractContent
{
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?Service $service=null;
    #[ORM\Column(nullable:true)] private ?int $durationMinutes=null;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $purpose=null;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $discomfort=null;
    /** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $preparation=[];
    /** @var list<string> */ #[ORM\Column(type:Types::JSON)] private array $requiredDocuments=[];
    #[ORM\Column] private bool $referralRequired=false; #[ORM\Column] private bool $appointmentRequired=false; #[ORM\Column] private bool $companionRequired=false; #[ORM\Column(nullable:true)] private ?bool $canDriveAfter=null;
    /** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $aftercare=[];
    public function setService(?Service $v):self{if($v)$this->assertSameTenant($v);$this->service=$v;return $this;}
    public function setPurpose(?string $v):self{$this->purpose=$v;return $this;} public function setDurationMinutes(?int $v):self{$this->durationMinutes=$v;return $this;}
    /** @param array<string,mixed> $v */ public function setPreparation(array $v):self{$this->preparation=$v;return $this;} /** @param list<string> $v */ public function setRequiredDocuments(array $v):self{$this->requiredDocuments=$v;return $this;}
}
