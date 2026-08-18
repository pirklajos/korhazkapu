<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name:'content_revision')] #[ORM\UniqueConstraint(name:'uniq_content_revision_version',columns:['content_type','content_id','version_number'])]
class ContentRevision extends AbstractTenantEntity
{
    #[ORM\Column(length:120)] private string $contentType;
    #[ORM\Column(length:36)] private string $contentId;
    #[ORM\Column] private int $versionNumber;
    /** @var array<string,mixed> */ #[ORM\Column(type:Types::JSON)] private array $snapshot;
    #[ORM\Column(type:Types::TEXT,nullable:true)] private ?string $summary;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable:true,onDelete:'SET NULL')] private ?User $author;
    /** @param array<string,mixed> $snapshot */
    public function __construct(Institution $institution,string $contentType,string $contentId,int $versionNumber,array $snapshot,?string $summary,?User $author){parent::__construct($institution);$this->contentType=$contentType;$this->contentId=$contentId;$this->versionNumber=$versionNumber;$this->snapshot=$snapshot;$this->summary=$summary;$this->author=$author;}
    public function getContentType():string{return $this->contentType;} public function getContentId():string{return $this->contentId;} public function getVersionNumber():int{return $this->versionNumber;} /** @return array<string,mixed> */ public function getSnapshot():array{return $this->snapshot;} public function getSummary():?string{return $this->summary;} public function getAuthor():?User{return $this->author;}
}
