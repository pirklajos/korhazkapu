<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Tenant\TenantContext;

final readonly class TenantRoleChecker
{
    public function __construct(private TenantContext $context) {}
    public function hasRole(User $user, string $role): bool
    {
        if (in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)) return true;
        $institution = $this->context->getInstitution();
        if (!$institution) return false;
        foreach ($user->getMemberships() as $membership) {
            if ($membership->getInstitution()->getId()->equals($institution->getId())) return $membership->hasRole($role);
        }
        return false;
    }
}
