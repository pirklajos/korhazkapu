<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\AuditLog;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminAuditController extends AbstractController
{
    #[Route('/i/{institutionSlug}/admin/audit',name:'tenant_admin_audit',methods:['GET'])]
    public function __invoke(TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_AUDITOR')))throw $this->createAccessDeniedException();$institution=$context->requireInstitution();return $this->render('admin/audit/index.html.twig',['institution'=>$institution,'events'=>$em->getRepository(AuditLog::class)->findBy(['institution'=>$institution],['createdAt'=>'DESC'],100)]);
    }
}
