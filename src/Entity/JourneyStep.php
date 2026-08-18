<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'journey_step')]
class JourneyStep extends AbstractTenantEntity
{
    #[ORM\ManyToOne(inversedBy:'steps')] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private PatientJourney $journey;
    #[ORM\Column] private int $position; #[ORM\Column(length:255)] private string $title; #[ORM\Column(type:Types::TEXT)] private string $description;
    #[ORM\Column(length:255,nullable:true)] private ?string $location=null; #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $action=null;
    /** @var list<string> */ #[ORM\Column(type:Types::JSON)] private array $requiredDocuments=[];
    public function __construct(Institution $institution,PatientJourney $journey,int $position,string $title,string $description){parent::__construct($institution);$this->assertSameTenant($journey);$this->journey=$journey;$this->position=$position;$this->title=$title;$this->description=$description;$journey->addStep($this);}
    public function getJourney():PatientJourney{return $this->journey;} public function getPosition():int{return $this->position;} public function setPosition(int $v):self{$this->position=$v;return $this;}
    public function getTitle():string{return $this->title;} public function setTitle(string $v):self{$this->title=$v;return $this;} public function getDescription():string{return $this->description;} public function setDescription(string $v):self{$this->description=$v;return $this;}
    public function getLocation():?string{return $this->location;} public function setLocation(?string $v):self{$this->location=$v;return $this;} public function getAction():?string{return $this->action;} public function setAction(?string $v):self{$this->action=$v;return $this;}
    /** @return list<string> */ public function getRequiredDocuments():array{return $this->requiredDocuments;} /** @param list<string> $v */ public function setRequiredDocuments(array $v):self{$this->requiredDocuments=$v;return $this;}
}
