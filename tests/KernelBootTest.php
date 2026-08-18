<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class KernelBootTest extends KernelTestCase
{
    public function testKernelBootsInTestEnvironment(): void
    {
        self::bootKernel();

        self::assertSame('test', self::$kernel->getEnvironment());
        self::assertNotNull(self::getContainer());
    }
}
