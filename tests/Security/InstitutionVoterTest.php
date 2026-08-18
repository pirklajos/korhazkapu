<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\Institution;
use App\Entity\Membership;
use App\Entity\User;
use App\Security\InstitutionVoter;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class InstitutionVoterTest extends TestCase
{
    public function testInstitutionAdminCannotEditAnotherTenant(): void
    {
        $own = new Institution('Saját', 'Saját', 'sajat', 'Budapest');
        $other = new Institution('Másik', 'Másik', 'masik', 'Szeged');
        $user = new User('admin@example.test', 'Admin');
        new Membership($user, $own, ['ROLE_INSTITUTION_ADMIN']);
        $context = new TenantContext();
        $context->setInstitution($other);
        $voter = new InstitutionVoter($context, new TenantRoleChecker($context));
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $other, [InstitutionVoter::EDIT]));
    }

    public function testInstitutionAdminCanEditOwnTenant(): void
    {
        $own = new Institution('Saját', 'Saját', 'sajat', 'Budapest');
        $user = new User('admin@example.test', 'Admin');
        new Membership($user, $own, ['ROLE_INSTITUTION_ADMIN']);
        $context = new TenantContext();
        $context->setInstitution($own);
        $voter = new InstitutionVoter($context, new TenantRoleChecker($context));
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $own, [InstitutionVoter::EDIT]));
    }
}
