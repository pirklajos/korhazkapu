<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InstitutionTheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<InstitutionTheme> */
final class InstitutionThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, InstitutionTheme::class); }
}
