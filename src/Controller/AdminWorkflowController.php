<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\AbstractContent;
use App\Entity\Announcement;
use App\Entity\ContentRevision;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\User;
use App\Tenant\TenantContext;
use App\Security\TenantRoleChecker;
use App\Workflow\ContentWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminWorkflowController extends AbstractController
{
    private const TYPES=['page'=>InformationPage::class,'guide'=>ProcedureGuide::class,'journey'=>PatientJourney::class,'announcement'=>Announcement::class];
    #[Route('/i/{institutionSlug}/admin/content/{type}/{id}/transition/{target}',name:'tenant_admin_content_transition',methods:['POST'])]
    public function transition(string $type,string $id,string $target,Request $request,TenantContext $context,EntityManagerInterface $em,ContentWorkflow $workflow):Response
    {
        $content=$this->content($type,$id,$context,$em);$user=$this->getUser();if(!$user instanceof User)throw $this->createAccessDeniedException();if(!$this->isCsrfTokenValid('transition_'.$id.'_'.$target,(string)$request->request->get('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');
        try{$workflow->transition($content,ContentStatus::from($target),$user,$request->request->getString('comment'));$this->addFlash('success','A tartalom állapota frissült.');}catch(\ValueError|\DomainException $e){$this->addFlash('error',$e->getMessage());}return $this->back($content,$type);
    }
    #[Route('/i/{institutionSlug}/admin/content/{type}/{id}/restore/{revisionId}',name:'tenant_admin_content_restore',methods:['POST'])]
    public function restore(string $type,string $id,string $revisionId,Request $request,TenantContext $context,EntityManagerInterface $em,ContentWorkflow $workflow,TenantRoleChecker $roles):Response
    {
        $content=$this->content($type,$id,$context,$em);$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();if(!$this->isCsrfTokenValid('restore_'.$revisionId,(string)$request->request->get('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');$revision=$em->getRepository(ContentRevision::class)->find($revisionId)??throw $this->createNotFoundException();if(!$revision->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();
        try{$workflow->restore($content,$revision,$user);$this->addFlash('success','A verzió piszkozatként visszaállítva.');}catch(\DomainException $e){$this->addFlash('error',$e->getMessage());}return $this->back($content,$type);
    }
    private function content(string $type,string $id,TenantContext $context,EntityManagerInterface $em):AbstractContent{$class=self::TYPES[$type]??throw $this->createNotFoundException();$content=$em->getRepository($class)->find($id)??throw $this->createNotFoundException();if(!$content->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();return $content;}
    private function back(AbstractContent $content,string $type):Response{return $this->redirectToRoute('tenant_admin_crud_edit',['institutionSlug'=>$content->getInstitution()->getSlug(),'type'=>$type,'id'=>$content->getId()]);}
}
