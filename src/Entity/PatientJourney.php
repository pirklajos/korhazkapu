<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'patient_journey')] #[ORM\UniqueConstraint(name:'uniq_journey_slug_tenant',columns:['institution_id','slug'])]
class PatientJourney extends AbstractContent
{
    #[ORM\Column(length:255,nullable:true)] private ?string $targetAudience=null;
    /** @var Collection<int,JourneyStep> */ #[ORM\OneToMany(mappedBy:'journey',targetEntity:JourneyStep::class,cascade:['persist'],orphanRemoval:true)] #[ORM\OrderBy(['position'=>'ASC'])] private Collection $steps;
    public function __construct(Institution $institution,string $title,string $slug){parent::__construct($institution,$title,$slug);$this->steps=new ArrayCollection();}
    public function getTargetAudience():?string{return $this->targetAudience;} public function setTargetAudience(?string $v):self{$this->targetAudience=$v;return $this;} /** @return Collection<int,JourneyStep> */ public function getSteps():Collection{return $this->steps;}
    public function addStep(JourneyStep $v):self{$this->assertSameTenant($v);if(!$this->steps->contains($v))$this->steps->add($v);return $this;}
}
