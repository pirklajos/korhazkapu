<?php

declare(strict_types=1);

namespace App\Tests\Tenant;

use App\Entity\Institution;
use App\Repository\InstitutionLookupInterface;
use App\Tenant\TenantResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class TenantResolverTest extends TestCase
{
    private Institution $institution;

    protected function setUp(): void
    {
        $this->institution = new Institution('Demo', 'Demo', 'demo-korhaz', 'Budapest');
    }

    public function testResolvesDevelopmentPathBeforeDomain(): void
    {
        $resolver = new TenantResolver($this->lookup(slug: $this->institution));
        self::assertSame($this->institution, $resolver->resolve(Request::create('https://unknown.test/i/demo-korhaz')));
    }

    public function testResolvesInstitutionDomain(): void
    {
        $resolver = new TenantResolver($this->lookup(domain: $this->institution));
        self::assertSame($this->institution, $resolver->resolve(Request::create('https://demo.example.test/')));
    }

    public function testLocalhostWithoutInstitutionPathRemainsPlatformContext(): void
    {
        $resolver = new TenantResolver($this->lookup());
        self::assertNull($resolver->resolve(Request::create('http://localhost/')));
    }

    private function lookup(?Institution $slug = null, ?Institution $domain = null): InstitutionLookupInterface
    {
        return new class($slug, $domain) implements InstitutionLookupInterface {
            public function __construct(private readonly ?Institution $slug, private readonly ?Institution $domain) {}
            public function findActiveBySlug(string $slug): ?Institution { return $this->slug; }
            public function findActiveByDomain(string $domain): ?Institution { return $this->domain; }
        };
    }
}
