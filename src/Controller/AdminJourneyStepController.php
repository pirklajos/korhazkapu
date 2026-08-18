<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\JourneyStep;
use App\Entity\PatientJourney;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminJourneyStepController extends AbstractController
{
    #[Route('/i/{institutionSlug}/admin/content/journey/{journeyId}/steps/new',name:'tenant_admin_journey_step_new',methods:['GET','POST'])]
    public function create(string $journeyId,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $this->denyUnlessEditor($roles);$journey=$this->journey($journeyId,$context,$em);$step=new JourneyStep($context->requireInstitution(),$journey,$journey->getSteps()->count()+1,'','');
        return $this->handle($step,$request,$em,true);
    }

    #[Route('/i/{institutionSlug}/admin/content/journey/{journeyId}/steps/{stepId}/edit',name:'tenant_admin_journey_step_edit',methods:['GET','POST'])]
    public function edit(string $journeyId,string $stepId,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $this->denyUnlessEditor($roles);$journey=$this->journey($journeyId,$context,$em);$step=$em->getRepository(JourneyStep::class)->find($stepId)??throw $this->createNotFoundException();
        if(!$step->getJourney()->getId()->equals($journey->getId()))throw $this->createAccessDeniedException();return $this->handle($step,$request,$em,false);
    }

    #[Route('/i/{institutionSlug}/admin/content/journey/{journeyId}/steps/{stepId}/delete',name:'tenant_admin_journey_step_delete',methods:['POST'])]
    public function delete(string $journeyId,string $stepId,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $this->denyUnlessEditor($roles);$journey=$this->journey($journeyId,$context,$em);$step=$em->getRepository(JourneyStep::class)->find($stepId)??throw $this->createNotFoundException();if(!$step->getJourney()->getId()->equals($journey->getId()))throw $this->createAccessDeniedException();
        if(!$this->isCsrfTokenValid('delete_step_'.$stepId,(string)$request->request->get('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');$em->remove($step);$em->flush();$this->addFlash('success','A betegút lépése törölve.');return $this->redirectToJourney($journey);
    }

    private function handle(JourneyStep $step,Request $request,EntityManagerInterface $em,bool $new):Response
    {
        $form=$this->createFormBuilder($step)->add('position',IntegerType::class,['label'=>'Sorrend'])->add('title',TextType::class,['label'=>'Lépés címe'])->add('description',TextareaType::class,['label'=>'Leírás'])->add('location',TextType::class,['required'=>false,'label'=>'Helyszín'])->add('action',TextareaType::class,['required'=>false,'label'=>'Beteg teendője'])->add('documents',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Szükséges dokumentumok (soronként)','data'=>implode("\n",$step->getRequiredDocuments())])->getForm();$form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){$documents=array_values(array_filter(array_map('trim',preg_split('/\R/',(string)$form->get('documents')->getData())?:[])));$step->setRequiredDocuments($documents);$em->persist($step);$em->flush();$this->addFlash('success',$new?'A betegút lépése létrejött.':'A betegút lépése frissült.');return $this->redirectToJourney($step->getJourney());}
        return $this->render('admin/content/journey_step_form.html.twig',['form'=>$form,'step'=>$step,'journey'=>$step->getJourney(),'institution'=>$step->getInstitution(),'isNew'=>$new]);
    }
    private function journey(string $id,TenantContext $context,EntityManagerInterface $em):PatientJourney{$journey=$em->getRepository(PatientJourney::class)->find($id)??throw $this->createNotFoundException();if(!$journey->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();return $journey;}
    private function redirectToJourney(PatientJourney $journey):Response{return $this->redirectToRoute('tenant_admin_crud_edit',['institutionSlug'=>$journey->getInstitution()->getSlug(),'type'=>'journey','id'=>$journey->getId()]);}
    private function denyUnlessEditor(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();}
}
