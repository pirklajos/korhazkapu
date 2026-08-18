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
    public function getService():?Service{return $this->service;} public function getPurpose():?string{return $this->purpose;} public function getDurationMinutes():?int{return $this->durationMinutes;}
    /** @param array<string,mixed> $v */ public function setPreparation(array $v):self{$this->preparation=$v;return $this;} /** @param list<string> $v */ public function setRequiredDocuments(array $v):self{$this->requiredDocuments=$v;return $this;}
    public function getDiscomfort():?string{return $this->discomfort;} public function setDiscomfort(?string $v):self{$this->discomfort=$v;return $this;}
    /** @return array<string,mixed> */ public function getPreparation():array{return $this->preparation;} /** @return list<string> */ public function getRequiredDocuments():array{return $this->requiredDocuments;}
    public function isReferralRequired():bool{return $this->referralRequired;} public function setReferralRequired(bool $v):self{$this->referralRequired=$v;return $this;} public function isAppointmentRequired():bool{return $this->appointmentRequired;} public function setAppointmentRequired(bool $v):self{$this->appointmentRequired=$v;return $this;}
    public function isCompanionRequired():bool{return $this->companionRequired;} public function setCompanionRequired(bool $v):self{$this->companionRequired=$v;return $this;} public function getCanDriveAfter():?bool{return $this->canDriveAfter;} public function setCanDriveAfter(?bool $v):self{$this->canDriveAfter=$v;return $this;}
    /** @return array<string,mixed> */ public function getAftercare():array{return $this->aftercare;} /** @param array<string,mixed> $v */ public function setAftercare(array $v):self{$this->aftercare=$v;return $this;}
}
