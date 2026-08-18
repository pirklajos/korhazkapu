<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Institution;
use App\Entity\User;
use App\Entity\AbstractContent;
use App\Entity\Announcement;
use App\Entity\AuditLog;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Repository\InstitutionRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard', methods: ['GET'])]
    #[Route('/i/{institutionSlug}/admin', name: 'tenant_admin_dashboard', requirements: ['institutionSlug' => '[a-z0-9]+(?:-[a-z0-9]+)*'], methods: ['GET'])]
    public function __invoke(TenantContext $context, InstitutionRepository $institutions,EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) throw $this->createAccessDeniedException();
        $platformAdmin = in_array('ROLE_PLATFORM_ADMIN', $user->getRoles(), true);
        if (!$platformAdmin && !$context->getInstitution()) throw $this->createAccessDeniedException('Intézményi környezet szükséges.');

        $content=[];$institution=$context->getInstitution();if($institution)foreach([InformationPage::class,ProcedureGuide::class,PatientJourney::class,Announcement::class] as $class)$content=array_merge($content,$em->getRepository($class)->findBy(['institution'=>$institution]));$now=new \DateTimeImmutable();$reviewLimit=$now->modify('+30 days');
        return $this->render('admin/dashboard.html.twig', [
            'currentInstitution' => $context->getInstitution(),
            'institutions' => $platformAdmin ? $institutions->findBy(['status' => Institution::STATUS_ACTIVE], ['name' => 'ASC']) : [],
            'isPlatformAdmin' => $platformAdmin,
            'workflowStats'=>['draft'=>count(array_filter($content,fn(AbstractContent $v)=>$v->getStatus()===ContentStatus::Draft)),'review'=>count(array_filter($content,fn(AbstractContent $v)=>in_array($v->getStatus(),[ContentStatus::MedicalReview,ContentStatus::CommunicationReview,ContentStatus::Approved],true))),'published'=>count(array_filter($content,fn(AbstractContent $v)=>$v->getStatus()===ContentStatus::Published)),'due'=>count(array_filter($content,fn(AbstractContent $v)=>$v->getReviewDueAt()&&$v->getReviewDueAt()<=$reviewLimit))],
            'recentAudit'=>$institution?$em->getRepository(AuditLog::class)->findBy(['institution'=>$institution],['createdAt'=>'DESC'],5):[],
        ]);
    }
}
