<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Institution;
use App\Entity\User;
use App\Repository\InstitutionRepository;
use App\Tenant\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: ['GET'])]
    #[Route('/i/{institutionSlug}/admin', name: 'tenant_admin_dashboard', requirements: ['institutionSlug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(TenantContext $context, InstitutionRepository $institutions): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) throw $this->createAccessDeniedException();
        $platformAdmin = in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true);
        if (!$platformAdmin && !$context->getInstitution()) throw $this->createAccessDeniedException('Intézményi környezet szükséges.');

        return $this->render('admin/dashboard.html.twig', [
            'currentInstitution' => $context->getInstitution(),
            'institutions' => $platformAdmin ? $institutions->findBy(['status' => Institution::STATUS_ACTIVE], ['name' => 'ASC']) : [],
            'isPlatformAdmin' => $platformAdmin,
        ]);
    }
}
