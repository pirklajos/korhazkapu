<?php

declare(strict_types=1);

namespace App\Controller;

use App\Tenant\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'platform_home', methods: ['GET'])]
    public function platform(): Response { return $this->render('home/platform.html.twig'); }

    #[Route('/i/{institutionSlug}', name: 'tenant_home', requirements: ['institutionSlug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function tenant(TenantContext $context): Response
    {
        return $this->render('home/tenant.html.twig', ['institution' => $context->requireInstitution()]);
    }
}
