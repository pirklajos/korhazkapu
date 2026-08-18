<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Institution;
use App\Entity\User;
use App\Tenant\TenantContext;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class InstitutionVoter extends Voter
{
    public const VIEW = 'INSTITUTION_VIEW';
    public const EDIT = 'INSTITUTION_EDIT';

    public function __construct(private readonly TenantContext $context, private readonly TenantRoleChecker $roles) {}
    protected function supports(string $attribute, mixed $subject): bool { return in_array($attribute, [self::VIEW, self::EDIT], true) && $subject instanceof Institution; }
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;
        if (in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)) return true;
        if (!$this->context->getInstitution()?->getId()->equals($subject->getId())) return false;
        return $attribute === self::VIEW
            ? $this->roles->hasRole($user, 'ROLE_AUDITOR') || $this->roles->hasRole($user, 'ROLE_EDITOR') || $this->roles->hasRole($user, 'ROLE_INSTITUTION_ADMIN')
            : $this->roles->hasRole($user, 'ROLE_INSTITUTION_ADMIN');
    }
}
