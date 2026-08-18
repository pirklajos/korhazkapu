<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity] #[ORM\Table(name:'template_adoption')] #[ORM\UniqueConstraint(name:'uniq_template_adoption',columns:['institution_id','template_id'])]
class TemplateAdoption extends AbstractTenantEntity
{
 #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private ContentTemplate $template;#[ORM\ManyToOne] #[ORM\JoinColumn(nullable:false,onDelete:'CASCADE')] private InformationPage $page;#[ORM\Column] private int $adoptedVersion;
 public function __construct(Institution $institution,ContentTemplate $template,InformationPage $page){if(!$page->getInstitution()->getId()->equals($institution->getId()))throw new \DomainException('A sablonátvétel oldala csak ugyanahhoz az intézményhez tartozhat.');parent::__construct($institution);$this->template=$template;$this->page=$page;$this->adoptedVersion=$template->getVersion();}public function getTemplate():ContentTemplate{return $this->template;}public function getPage():InformationPage{return $this->page;}public function getAdoptedVersion():int{return $this->adoptedVersion;}public function markUpdated():self{$this->adoptedVersion=$this->template->getVersion();return $this;}public function hasUpdate():bool{return $this->template->getVersion()>$this->adoptedVersion;}
}
