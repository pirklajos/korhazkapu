<?php
declare(strict_types=1);
namespace App\Tests\Entity;
use App\Entity\Building;
use App\Entity\Institution;
use App\Entity\Site;
use PHPUnit\Framework\TestCase;

final class TenantRelationTest extends TestCase
{
    public function testCrossTenantStructureRelationIsRejected():void
    {
        $first=new Institution('Első','E','elso','Budapest');$second=new Institution('Második','M','masodik','Szeged');$site=new Site($first,'Telephely','telephely','Budapest');
        $this->expectException(\DomainException::class); new Building($second,$site,'Tiltott épület');
    }
}
