<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MembershipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MembershipRepository::class)]
#[ORM\Table(name: 'membership')]
#[ORM\UniqueConstraint(name: 'uniq_membership_user_institution', columns: ['user_id', 'institution_id'])]
class Membership implements TenantOwnedEntity
{
    public const INSTITUTION_ROLES = ['ROLE_INSTITUTION_ADMIN', 'ROLE_SITE_ADMIN', 'ROLE_EDITOR', 'ROLE_MEDICAL_REVIEWER', 'ROLE_PUBLISHER', 'ROLE_AUDITOR'];

    #[ORM\Id] #[ORM\Column(type: UuidType::NAME, unique: true)] private Uuid $id;
    #[ORM\ManyToOne(inversedBy: 'memberships')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $user;
    #[ORM\ManyToOne(inversedBy: 'memberships')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Institution $institution;
    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)] #[Assert\All([new Assert\Choice(choices: self::INSTITUTION_ROLES)])] private array $roles;
    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)] private array $siteIds = [];
    #[ORM\Column] private bool $active = true;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private \DateTimeImmutable $createdAt;

    /** @param list<string> $roles */
    public function __construct(User $user, Institution $institution, array $roles)
    {
        $this->id = Uuid::v7(); $this->user = $user; $this->institution = $institution;
        $this->roles = array_values(array_unique($roles)); $this->createdAt = new \DateTimeImmutable();
        $user->addMembership($this);
    }
    public function getId(): Uuid { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getInstitution(): Institution { return $this->institution; }
    /** @return list<string> */ public function getRoles(): array { return $this->roles; }
    public function hasRole(string $role): bool { return $this->active && in_array($role, $this->roles, true); }
    /** @return list<string> */ public function getSiteIds(): array { return $this->siteIds; }
    public function isActive(): bool { return $this->active; }
}
