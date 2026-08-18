<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractTenantEntity implements TenantOwnedEntity
{
    #[ORM\Id] #[ORM\Column(type: UuidType::NAME, unique: true)] protected Uuid $id;
    #[ORM\ManyToOne] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] protected Institution $institution;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] protected \DateTimeImmutable $createdAt;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] protected \DateTimeImmutable $updatedAt;

    public function __construct(Institution $institution)
    {
        $this->id = Uuid::v7(); $this->institution = $institution;
        $this->createdAt = new \DateTimeImmutable(); $this->updatedAt = $this->createdAt;
    }
    public function getId(): Uuid { return $this->id; }
    public function getInstitution(): Institution { return $this->institution; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    #[ORM\PreUpdate] public function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
    protected function assertSameTenant(TenantOwnedEntity $entity): void
    {
        if (!$this->institution->getId()->equals($entity->getInstitution()->getId())) throw new \DomainException('Cross-tenant relation is forbidden.');
    }
}
