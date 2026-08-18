<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Building;
use App\Entity\ContactPoint;
use App\Entity\Department;
use App\Entity\InformationPage;
use App\Entity\Floor;
use App\Entity\PatientJourney;
use App\Entity\Service;
use App\Entity\Site;
use App\Entity\ProcedureGuide;
use App\Entity\Room;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminContentController extends AbstractController
{
    #[Route('/admin/content',name:'admin_content',methods:['GET'])]
    #[Route('/i/{institutionSlug}/admin/content',name:'tenant_admin_content',methods:['GET'])]
    public function __invoke(TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $user=$this->getUser(); $institution=$context->requireInstitution();
        if (!$user instanceof User || (!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true) && !$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN') && !$roles->hasRole($user,'ROLE_EDITOR') && !$roles->hasRole($user,'ROLE_AUDITOR'))) throw $this->createAccessDeniedException();
        $find=fn(string $class):array=>$em->getRepository($class)->findBy(['institution'=>$institution]);
        return $this->render('admin/content/index.html.twig',['institution'=>$institution,'sites'=>$find(Site::class),'buildings'=>$find(Building::class),'floors'=>$find(Floor::class),'rooms'=>$find(Room::class),'departments'=>$find(Department::class),'services'=>$find(Service::class),'contacts'=>$find(ContactPoint::class),'pages'=>$find(InformationPage::class),'guides'=>$find(ProcedureGuide::class),'journeys'=>$find(PatientJourney::class),'announcements'=>$find(Announcement::class)]);
    }
}
