<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\Entity\TenantOwnedEntity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

final class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!is_a($targetEntity->getName(), TenantOwnedEntity::class, true)) return '';
        return sprintf('%s.institution_id = %s', $targetTableAlias, $this->getParameter('institution_id'));
    }
}
