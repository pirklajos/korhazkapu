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
}
