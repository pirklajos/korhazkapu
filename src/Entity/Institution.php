<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstitutionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstitutionRepository::class)]
#[ORM\Table(name: 'institution')]
#[ORM\UniqueConstraint(name: 'uniq_institution_slug', columns: ['slug'])]
#[ORM\UniqueConstraint(name: 'uniq_institution_primary_domain', columns: ['primary_domain'])]
#[ORM\HasLifecycleCallbacks]
class Institution
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    private string $shortName;

    #[ORM\Column(length: 120, unique: true)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
    private string $slug;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_ACTIVE, self::STATUS_INACTIVE])]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $primaryDomain = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $domains = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $centralAddress;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $maintainerName = null;

    #[ORM\Column(length: 10)]
    private string $defaultLocale = 'hu';

    #[ORM\Column(length: 64)]
    private string $timezone = 'Europe/Budapest';

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    private ?string $privacyUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    private ?string $legalUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'institution', targetEntity: InstitutionTheme::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?InstitutionTheme $theme = null;

    /** @var Collection<int, Membership> */
    #[ORM\OneToMany(mappedBy: 'institution', targetEntity: Membership::class, orphanRemoval: true)]
    private Collection $memberships;

    public function __construct(string $name, string $shortName, string $slug, string $centralAddress)
    {
        $this->id = Uuid::v7();
        $this->name = $name;
        $this->shortName = $shortName;
        $this->slug = $slug;
        $this->centralAddress = $centralAddress;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->memberships = new ArrayCollection();
    }

    public function getId(): Uuid { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getShortName(): string { return $this->shortName; }
    public function setShortName(string $shortName): self { $this->shortName = $shortName; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getPrimaryDomain(): ?string { return $this->primaryDomain; }
    public function setPrimaryDomain(?string $domain): self { $this->primaryDomain = $domain ? strtolower($domain) : null; return $this; }
    /** @return list<string> */
    public function getDomains(): array { return $this->domains; }
    /** @param list<string> $domains */
    public function setDomains(array $domains): self { $this->domains = array_values(array_unique(array_map('strtolower', $domains))); return $this; }
    public function getCentralAddress(): string { return $this->centralAddress; }
    public function setCentralAddress(string $address): self { $this->centralAddress = $address; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getMaintainerName(): ?string { return $this->maintainerName; }
    public function setMaintainerName(?string $name): self { $this->maintainerName = $name; return $this; }
    public function getDefaultLocale(): string { return $this->defaultLocale; }
    public function setDefaultLocale(string $locale): self { $this->defaultLocale = $locale; return $this; }
    public function getTimezone(): string { return $this->timezone; }
    public function setTimezone(string $timezone): self { $this->timezone = $timezone; return $this; }
    public function getPrivacyUrl(): ?string { return $this->privacyUrl; }
    public function setPrivacyUrl(?string $url): self { $this->privacyUrl = $url; return $this; }
    public function getLegalUrl(): ?string { return $this->legalUrl; }
    public function setLegalUrl(?string $url): self { $this->legalUrl = $url; return $this; }
    public function getTheme(): ?InstitutionTheme { return $this->theme; }
    public function setTheme(InstitutionTheme $theme): self { $this->theme = $theme; if ($theme->getInstitution() !== $this) $theme->setInstitution($this); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    #[ORM\PreUpdate]
    public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
