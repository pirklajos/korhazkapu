<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Tenant\TenantContext;
use App\Tenant\TenantResolver;
use App\Entity\User;
use App\Repository\InstitutionLookupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
final readonly class TenantRequestSubscriber
{
    public function __construct(
        private TenantResolver $resolver,
        private TenantContext $context,
        private InstitutionLookupInterface $institutions,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {}
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) return;
        $this->context->clear();
        $request = $event->getRequest();
        $institution = $this->resolver->resolve($request);
        $user = $this->security->getUser();
        if (!$institution && $user instanceof User && in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true)) {
            $selectedSlug = $request->getSession()->get('platform_tenant_slug');
            if (is_string($selectedSlug)) $institution = $this->institutions->findActiveBySlug($selectedSlug);
        }
        if ($institution) {
            $this->context->setInstitution($institution);
            $filter = $this->entityManager->getFilters()->enable('tenant');
            $filter->setParameter('institution_id', $institution->getId()->toRfc4122());
        } elseif (str_starts_with($request->getPathInfo(), '/i/')) {
            throw new NotFoundHttpException('A kért intézmény nem található.');
        }
    }
}
