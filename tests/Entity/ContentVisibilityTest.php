<?php
declare(strict_types=1);
namespace App\Tests\Entity;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\Institution;
use PHPUnit\Framework\TestCase;

final class ContentVisibilityTest extends TestCase
{
    public function testOnlyPublishedContentInsideWindowIsVisible():void
    {
        $institution=new Institution('Demo','Demo','demo','Budapest'); $page=new InformationPage($institution,'Tájékoztató','tajekoztato'); $now=new \DateTimeImmutable('2026-08-18 12:00:00');
        self::assertFalse($page->isPubliclyVisible($now));
        $page->setStatus(ContentStatus::Published)->setPublicationWindow($now->modify('-1 hour'),$now->modify('+1 hour')); self::assertTrue($page->isPubliclyVisible($now));
        $page->setPublicationWindow($now->modify('-2 hours'),$now->modify('-1 hour')); self::assertFalse($page->isPubliclyVisible($now));
    }
}
