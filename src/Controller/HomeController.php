<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Department;
use App\Entity\PatientJourney;
use App\Entity\Service;
use App\Entity\Site;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'platform_home', methods: ['GET'])]
    public function platform(): Response { return $this->render('home/platform.html.twig'); }

    #[Route('/i/{institutionSlug}', name: 'tenant_home', requirements: ['institutionSlug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function tenant(TenantContext $context, EntityManagerInterface $entityManager): Response
    {
        $institution = $context->requireInstitution();
        $forInstitution = static fn (string $class): array => $entityManager->getRepository($class)->findBy(['institution' => $institution]);
        $publicOnly = static fn (array $items): array => array_values(array_filter($items, static fn ($item): bool => $item->isPubliclyVisible()));

        return $this->render('home/tenant.html.twig', [
            'institution' => $institution,
            'sites' => $entityManager->getRepository(Site::class)->findBy(['institution' => $institution, 'active' => true], ['name' => 'ASC']),
            'departments' => array_slice($forInstitution(Department::class), 0, 3),
            'services' => array_slice($forInstitution(Service::class), 0, 6),
            'announcements' => array_slice($publicOnly($forInstitution(Announcement::class)), 0, 3),
            'journeys' => array_slice($publicOnly($forInstitution(PatientJourney::class)), 0, 3),
        ]);
    }
}
