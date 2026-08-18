<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractContent extends AbstractTenantEntity
{
    #[ORM\Column(length:255)] protected string $title;
    #[ORM\Column(length:140)] protected string $slug;
    #[ORM\Column(type:Types::TEXT,nullable:true)] protected ?string $summary=null;
    #[ORM\Column(enumType:ContentStatus::class)] protected ContentStatus $status=ContentStatus::Draft;
    #[ORM\Column(type:Types::DATETIME_IMMUTABLE,nullable:true)] protected ?\DateTimeImmutable $publishedAt=null;
    #[ORM\Column(type:Types::DATETIME_IMMUTABLE,nullable:true)] protected ?\DateTimeImmutable $expiresAt=null;
    #[ORM\Column(type:Types::DATE_IMMUTABLE,nullable:true)] protected ?\DateTimeImmutable $reviewDueAt=null;
    public function __construct(Institution $institution,string $title,string $slug){parent::__construct($institution);$this->title=$title;$this->slug=$slug;}
    public function getTitle():string{return $this->title;} public function setTitle(string $v):self{$this->title=$v;return $this;} public function getSlug():string{return $this->slug;}
    public function setSlug(string $v):self{$this->slug=$v;return $this;}
    public function getSummary():?string{return $this->summary;} public function setSummary(?string $v):self{$this->summary=$v;return $this;}
    public function getStatus():ContentStatus{return $this->status;} public function setStatus(ContentStatus $v):self{$this->status=$v;return $this;}
    public function getPublishedAt():?\DateTimeImmutable{return $this->publishedAt;} public function getExpiresAt():?\DateTimeImmutable{return $this->expiresAt;} public function getReviewDueAt():?\DateTimeImmutable{return $this->reviewDueAt;} public function setReviewDueAt(?\DateTimeImmutable $v):self{$this->reviewDueAt=$v;return $this;}
    public function setPublicationWindow(?\DateTimeImmutable $from,?\DateTimeImmutable $until):self{$this->publishedAt=$from;$this->expiresAt=$until;return $this;}
    public function isPubliclyVisible(?\DateTimeImmutable $now=null):bool{$now??=new \DateTimeImmutable();return $this->status===ContentStatus::Published&&(!$this->publishedAt||$this->publishedAt<=$now)&&(!$this->expiresAt||$this->expiresAt>$now);}
}
