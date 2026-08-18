<?php

declare(strict_types=1);

namespace App\Tenant;

use App\Entity\Institution;

final class TenantContext
{
    private ?Institution $institution = null;

    public function setInstitution(Institution $institution): void { $this->institution = $institution; }
    public function clear(): void { $this->institution = null; }
    public function getInstitution(): ?Institution { return $this->institution; }
    public function requireInstitution(): Institution
    {
        return $this->institution ?? throw new TenantNotResolvedException('No institution is active in the current context.');
    }
}
