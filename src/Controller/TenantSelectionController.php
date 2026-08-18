<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\InstitutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_PLATFORM_ADMIN')]
final class TenantSelectionController extends AbstractController
{
    #[Route('/admin/tenant/{slug}', name: 'admin_tenant_select', methods: ['POST'])]
    public function select(string $slug, Request $request, InstitutionRepository $institutions): Response
    {
        if (!$this->isCsrfTokenValid('select_tenant_'.$slug, (string) $request->request->get('_token'))) throw $this->createAccessDeniedException('Érvénytelen CSRF token.');
        $institution = $institutions->findActiveBySlug($slug) ?? throw $this->createNotFoundException('Az intézmény nem található.');
        $request->getSession()->set('platform_tenant_slug', $institution->getSlug());
        return $this->redirectToRoute('tenant_home', ['institutionSlug' => $institution->getSlug()]);
    }
}
