<?php
declare(strict_types=1);
namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity] #[ORM\Table(name: 'site')] #[ORM\UniqueConstraint(name: 'uniq_site_slug_tenant', columns: ['institution_id','slug'])]
class Site extends AbstractTenantEntity
{
    #[ORM\Column(length: 255)] #[Assert\NotBlank] private string $name;
    #[ORM\Column(length: 120)] #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/')] private string $slug;
    #[ORM\Column(length: 255)] private string $address;
    #[ORM\Column(length: 500, nullable: true)] #[Assert\Url] private ?string $mapUrl = null;
    #[ORM\Column(type: Types::TEXT, nullable: true)] private ?string $accessibility = null;
    #[ORM\Column] private bool $active = true;
    public function __construct(Institution $institution, string $name, string $slug, string $address) { parent::__construct($institution); $this->name=$name; $this->slug=$slug; $this->address=$address; }
    public function getName(): string { return $this->name; } public function setName(string $v): self {$this->name=$v;return $this;}
    public function getSlug(): string { return $this->slug; } public function getAddress(): string { return $this->address; }
    public function setSlug(string $v): self {$this->slug=$v;return $this;}
    public function setAddress(string $v): self {$this->address=$v;return $this;} public function getMapUrl(): ?string{return $this->mapUrl;}
    public function setMapUrl(?string $v): self {$this->mapUrl=$v;return $this;} public function getAccessibility(): ?string{return $this->accessibility;}
    public function setAccessibility(?string $v): self {$this->accessibility=$v;return $this;} public function isActive(): bool{return $this->active;}
}
