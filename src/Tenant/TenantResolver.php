<?php

declare(strict_types=1);

namespace App\Tenant;

use App\Entity\Institution;
use App\Repository\InstitutionLookupInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class TenantResolver
{
    public function __construct(private InstitutionLookupInterface $institutions) {}

    public function resolve(Request $request): ?Institution
    {
        if (preg_match('#^/i/([a-z0-9]+(?:-[a-z0-9]+)*)(?:/|$)#', $request->getPathInfo(), $matches)) {
            return $this->institutions->findActiveBySlug($matches[1]);
        }

        $host = strtolower($request->getHost());
        if (in_array($host, ['localhost', '127.0.0.1'], true)) return null;

        return $this->institutions->findActiveByDomain($host);
    }
}
