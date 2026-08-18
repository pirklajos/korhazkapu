<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstitutionThemeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstitutionThemeRepository::class)]
#[ORM\Table(name: 'institution_theme')]
#[ORM\HasLifecycleCallbacks]
class InstitutionTheme implements TenantOwnedEntity
{
    private const COLOR_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'theme')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Institution $institution;

    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $primaryColor = '#005A70';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $secondaryColor = '#DCEFF3';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $accentColor = '#F2A900';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $textColor = '#172B35';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $backgroundColor = '#FFFFFF';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $dangerColor = '#B42318';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $warningColor = '#9A6700';
    #[ORM\Column(length: 7)] #[Assert\Regex(pattern: self::COLOR_PATTERN)] private string $successColor = '#067647';
    #[ORM\Column(length: 500, nullable: true)] private ?string $lightLogoPath = null;
    #[ORM\Column(length: 500, nullable: true)] private ?string $darkLogoPath = null;
    #[ORM\Column(length: 500, nullable: true)] private ?string $faviconPath = null;
    #[ORM\Column(length: 500, nullable: true)] private ?string $heroImagePath = null;
    #[ORM\Column(length: 40)] #[Assert\Choice(choices: ['system', 'humanist', 'serif'])] private string $fontFamily = 'system';
    #[ORM\Column(length: 40)] #[Assert\Choice(choices: ['standard', 'compact', 'brand'])] private string $headerVariant = 'standard';
    #[ORM\Column(length: 20)] #[Assert\Choice(choices: ['none', 'small', 'medium', 'large'])] private string $cornerRadius = 'medium';
    #[ORM\Column(length: 30)] #[Assert\Choice(choices: ['flat', 'bordered', 'soft-shadow'])] private string $componentStyle = 'bordered';
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private \DateTimeImmutable $createdAt;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private \DateTimeImmutable $updatedAt;

    public function __construct(Institution $institution)
    {
        $this->id = Uuid::v7();
        $this->institution = $institution;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $institution->setTheme($this);
    }

    public function getId(): Uuid { return $this->id; }
    public function getInstitution(): Institution { return $this->institution; }
    public function setInstitution(Institution $institution): self { $this->institution = $institution; return $this; }
    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function setPrimaryColor(string $value): self { $this->primaryColor = strtoupper($value); return $this; }
    public function getSecondaryColor(): string { return $this->secondaryColor; }
    public function setSecondaryColor(string $value): self { $this->secondaryColor = strtoupper($value); return $this; }
    public function getAccentColor(): string { return $this->accentColor; }
    public function setAccentColor(string $value): self { $this->accentColor = strtoupper($value); return $this; }
    public function getTextColor(): string { return $this->textColor; }
    public function setTextColor(string $value): self { $this->textColor = strtoupper($value); return $this; }
    public function getBackgroundColor(): string { return $this->backgroundColor; }
    public function setBackgroundColor(string $value): self { $this->backgroundColor = strtoupper($value); return $this; }
    public function getDangerColor(): string { return $this->dangerColor; }
    public function setDangerColor(string $value): self { $this->dangerColor = strtoupper($value); return $this; }
    public function getWarningColor(): string { return $this->warningColor; }
    public function setWarningColor(string $value): self { $this->warningColor = strtoupper($value); return $this; }
    public function getSuccessColor(): string { return $this->successColor; }
    public function setSuccessColor(string $value): self { $this->successColor = strtoupper($value); return $this; }
    public function getLightLogoPath(): ?string { return $this->lightLogoPath; }
    public function setLightLogoPath(?string $value): self { $this->lightLogoPath = $value; return $this; }
    public function getDarkLogoPath(): ?string { return $this->darkLogoPath; }
    public function setDarkLogoPath(?string $value): self { $this->darkLogoPath = $value; return $this; }
    public function getFaviconPath(): ?string { return $this->faviconPath; }
    public function setFaviconPath(?string $value): self { $this->faviconPath = $value; return $this; }
    public function getHeroImagePath(): ?string { return $this->heroImagePath; }
    public function setHeroImagePath(?string $value): self { $this->heroImagePath = $value; return $this; }
    public function getFontFamily(): string { return $this->fontFamily; }
    public function setFontFamily(string $value): self { $this->fontFamily = $value; return $this; }
    public function getHeaderVariant(): string { return $this->headerVariant; }
    public function setHeaderVariant(string $value): self { $this->headerVariant = $value; return $this; }
    public function getCornerRadius(): string { return $this->cornerRadius; }
    public function setCornerRadius(string $value): self { $this->cornerRadius = $value; return $this; }
    public function getComponentStyle(): string { return $this->componentStyle; }
    public function setComponentStyle(string $value): self { $this->componentStyle = $value; return $this; }
    /** @return array<string, string> */
    public function getCssVariables(): array
    {
        return ['--color-primary' => $this->primaryColor, '--color-secondary' => $this->secondaryColor, '--color-accent' => $this->accentColor, '--color-text' => $this->textColor, '--color-background' => $this->backgroundColor, '--color-danger' => $this->dangerColor, '--color-warning' => $this->warningColor, '--color-success' => $this->successColor];
    }
    #[ORM\PreUpdate] public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
