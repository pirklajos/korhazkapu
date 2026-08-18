<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Institution> */
final class InstitutionRepository extends ServiceEntityRepository implements InstitutionLookupInterface
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Institution::class); }

    public function findActiveBySlug(string $slug): ?Institution
    {
        return $this->findOneBy(['slug' => strtolower($slug), 'status' => Institution::STATUS_ACTIVE]);
    }

    public function findActiveByDomain(string $domain): ?Institution
    {
        $domain = strtolower(preg_replace('/:\d+$/', '', $domain) ?? $domain);
        $primary = $this->findOneBy(['primaryDomain' => $domain, 'status' => Institution::STATUS_ACTIVE]);
        if ($primary) return $primary;
        foreach ($this->findBy(['status' => Institution::STATUS_ACTIVE]) as $institution) {
            if (in_array($domain, $institution->getDomains(), true)) return $institution;
        }
        return null;
    }
}
