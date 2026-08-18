<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\JourneyStep;
use App\Entity\Building;
use App\Entity\Floor;
use App\Entity\PatientJourney;
use App\Entity\Room;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormError;
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
        $form=$this->createFormBuilder($step,['attr'=>['data-controller'=>'journey-location']])->add('position',IntegerType::class,['label'=>'Sorrend'])->add('title',TextType::class,['label'=>'Lépés címe'])->add('description',TextareaType::class,['label'=>'Leírás'])->add('building',EntityType::class,['class'=>Building::class,'choice_label'=>'name','required'=>false,'placeholder'=>'Válassz épületet','label'=>'Épület','attr'=>['data-journey-location-target'=>'building','data-action'=>'change->journey-location#buildingChanged']])->add('floor',EntityType::class,['class'=>Floor::class,'choice_label'=>static fn(Floor $floor):string=>$floor->getBuilding()->getName().' – '.$floor->getName(),'choice_attr'=>static fn(Floor $floor):array=>['data-building-id'=>(string)$floor->getBuilding()->getId()],'required'=>false,'placeholder'=>'Válassz szintet','label'=>'Szint','attr'=>['data-journey-location-target'=>'floor','data-action'=>'change->journey-location#floorChanged']])->add('room',EntityType::class,['class'=>Room::class,'choice_label'=>static fn(Room $room):string=>$room->getFloor()->getBuilding()->getName().' – '.$room->getFloor()->getName().' – '.$room->getName().($room->getNumber()?' ('.$room->getNumber().')':''),'choice_attr'=>static fn(Room $room):array=>['data-floor-id'=>(string)$room->getFloor()->getId()],'required'=>false,'placeholder'=>'Válassz helyiséget','label'=>'Helyiség','attr'=>['data-journey-location-target'=>'room']])->add('location',TextType::class,['required'=>false,'label'=>'További helyszíni útmutatás'])->add('action',TextareaType::class,['required'=>false,'label'=>'Beteg teendője'])->add('documents',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Szükséges dokumentumok (soronként)','data'=>implode("\n",$step->getRequiredDocuments())])->getForm();$form->handleRequest($request);
        if($form->isSubmitted()){$building=$step->getBuilding();$floor=$step->getFloor();$room=$step->getRoom();if($floor&&(!$building||!$floor->getBuilding()->getId()->equals($building->getId())))$form->get('floor')->addError(new FormError('A kiválasztott szint nem ehhez az épülethez tartozik.'));if($room&&(!$floor||!$room->getFloor()->getId()->equals($floor->getId())))$form->get('room')->addError(new FormError('A kiválasztott helyiség nem ehhez a szinthez tartozik.'));}
        if($form->isSubmitted()&&$form->isValid()){$documents=array_values(array_filter(array_map('trim',preg_split('/\R/',(string)$form->get('documents')->getData())?:[])));$step->setRequiredDocuments($documents);$em->persist($step);$em->flush();$this->addFlash('success',$new?'A betegút lépése létrejött.':'A betegút lépése frissült.');return $this->redirectToJourney($step->getJourney());}
        return $this->render('admin/content/journey_step_form.html.twig',['form'=>$form,'step'=>$step,'journey'=>$step->getJourney(),'institution'=>$step->getInstitution(),'isNew'=>$new]);
    }
    private function journey(string $id,TenantContext $context,EntityManagerInterface $em):PatientJourney{$journey=$em->getRepository(PatientJourney::class)->find($id)??throw $this->createNotFoundException();if(!$journey->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();return $journey;}
    private function redirectToJourney(PatientJourney $journey):Response{return $this->redirectToRoute('tenant_admin_crud_edit',['institutionSlug'=>$journey->getInstitution()->getSlug(),'type'=>'journey','id'=>$journey->getId()]);}
    private function denyUnlessEditor(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();}
}
