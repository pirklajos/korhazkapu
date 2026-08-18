<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'audit_log')]
class AuditLog extends AbstractTenantEntity
{
    #[ORM\Column(length:80)] private string $action;
    #[ORM\Column(length:120)] private string $subjectType;
    #[ORM\Column(length:36)] private string $subjectId;
    /** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $details;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?User $actor;
    /** @param array<string,mixed> $details */
    public function __construct(Institution $institution,string $action,string $subjectType,string $subjectId,array $details,?User $actor){parent::__construct($institution);$this->action=$action;$this->subjectType=$subjectType;$this->subjectId=$subjectId;$this->details=$details;$this->actor=$actor;}
    public function getAction():string{return $this->action;} public function getSubjectType():string{return $this->subjectType;} public function getSubjectId():string{return $this->subjectId;} /** @return array<string,mixed> */ public function getDetails():array{return $this->details;} public function getActor():?User{return $this->actor;}
}
