<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Membership;
use App\Entity\User;
use App\Tenant\TenantContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Membership> */
final class MembershipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly TenantContext $tenantContext)
    {
        parent::__construct($registry, Membership::class);
    }

    public function findCurrentForUser(User $user): ?Membership
    {
        return $this->findOneBy(['user' => $user, 'institution' => $this->tenantContext->requireInstitution(), 'active' => true]);
    }

    /** @return list<Membership> */
    public function findAllForCurrentInstitution(): array
    {
        return $this->findBy(['institution' => $this->tenantContext->requireInstitution()], ['createdAt' => 'DESC']);
    }
}
