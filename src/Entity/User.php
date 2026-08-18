<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id] #[ORM\Column(type: UuidType::NAME, unique: true)] private Uuid $id;
    #[ORM\Column(length: 180, unique: true)] #[Assert\Email] private string $email;
    #[ORM\Column(length: 160)] #[Assert\NotBlank] private string $displayName;
    #[ORM\Column] private string $password = '';
    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)] private array $platformRoles = [];
    #[ORM\Column] private bool $active = true;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private \DateTimeImmutable $createdAt;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] private \DateTimeImmutable $updatedAt;
    /** @var Collection<int, Membership> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Membership::class, orphanRemoval: true)] private Collection $memberships;

    public function __construct(string $email, string $displayName)
    {
        $this->id = Uuid::v7();
        $this->email = strtolower($email);
        $this->displayName = $displayName;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->memberships = new ArrayCollection();
    }

    public function getId(): Uuid { return $this->id; }
    public function getUserIdentifier(): string { return $this->email; }
    public function getEmail(): string { return $this->email; }
    public function getDisplayName(): string { return $this->displayName; }
    /** @return list<string> */
    public function getRoles(): array { return array_values(array_unique([...$this->platformRoles, 'ROLE_USER'])); }
    /** @param list<string> $roles */
    public function setPlatformRoles(array $roles): self { $this->platformRoles = $roles; return $this; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function isActive(): bool { return $this->active; }
    public function eraseCredentials(): void {}
    /** @return Collection<int, Membership> */
    public function getMemberships(): Collection { return $this->memberships; }
    public function addMembership(Membership $membership): self { if (!$this->memberships->contains($membership)) $this->memberships->add($membership); return $this; }
    #[ORM\PreUpdate] public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
