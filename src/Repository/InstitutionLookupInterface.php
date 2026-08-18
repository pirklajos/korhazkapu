<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Institution;

interface InstitutionLookupInterface
{
    public function findActiveBySlug(string $slug): ?Institution;
    public function findActiveByDomain(string $domain): ?Institution;
}
