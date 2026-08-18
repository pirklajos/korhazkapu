<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\AbstractContent;
use App\Entity\Building;
use App\Entity\ContentRevision;
use App\Entity\ContactPoint;
use App\Entity\ContentStatus;
use App\Entity\Department;
use App\Entity\InformationPage;
use App\Entity\Floor;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\Site;
use App\Entity\TenantOwnedEntity;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use App\Workflow\ContentWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AdminCrudController extends AbstractController
{
    private const TYPES=['site'=>Site::class,'building'=>Building::class,'floor'=>Floor::class,'room'=>Room::class,'department'=>Department::class,'service'=>Service::class,'contact'=>ContactPoint::class,'page'=>InformationPage::class,'guide'=>ProcedureGuide::class,'journey'=>PatientJourney::class,'announcement'=>Announcement::class];

    #[Route('/i/{institutionSlug}/admin/content/{type}/new',name:'tenant_admin_crud_new',methods:['GET','POST'])]
    public function create(string $type,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em,ContentWorkflow $workflow):Response
    {
        $this->denyUnlessEditor($roles); $institution=$context->requireInstitution();
        $first=fn(string $class)=>$em->getRepository($class)->findOneBy(['institution'=>$institution])??throw $this->createNotFoundException('Előbb hozd létre a szükséges szülőelemet.');
        $entity=match($type){'site'=>new Site($institution,'','',''),'building'=>new Building($institution,$first(Site::class),''),'floor'=>new Floor($institution,$first(Building::class),''),'room'=>new Room($institution,$first(Floor::class),''),'department'=>new Department($institution,'',''),'service'=>new Service($institution,'',''),'contact'=>new ContactPoint($institution,'','phone',''),'page'=>new InformationPage($institution,'',''),'guide'=>new ProcedureGuide($institution,'',''),'journey'=>new PatientJourney($institution,'',''),'announcement'=>new Announcement($institution,'',''),default=>throw $this->createNotFoundException()};
        $this->prefillParent($type,$entity,$request,$context,$em);
        return $this->handle($type,$entity,$request,$em,$workflow,true,true);
    }

    #[Route('/i/{institutionSlug}/admin/content/{type}/{id}/edit',name:'tenant_admin_crud_edit',methods:['GET','POST'])]
    public function edit(string $type,string $id,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em,ContentWorkflow $workflow):Response
    {
        $this->denyUnlessContentViewer($roles);$canEdit=$this->canEdit($roles);if($request->isMethod('POST')&&!$canEdit)throw $this->createAccessDeniedException(); $class=self::TYPES[$type]??throw $this->createNotFoundException();
        $entity=$em->getRepository($class)->find($id)??throw $this->createNotFoundException(); $this->assertCurrentTenant($entity,$context);
        return $this->handle($type,$entity,$request,$em,$workflow,$canEdit,false);
    }

    #[Route('/i/{institutionSlug}/admin/content/{type}/{id}/delete',name:'tenant_admin_crud_delete',methods:['POST'])]
    public function delete(string $type,string $id,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $this->denyUnlessEditor($roles); $class=self::TYPES[$type]??throw $this->createNotFoundException(); $entity=$em->getRepository($class)->find($id)??throw $this->createNotFoundException(); $this->assertCurrentTenant($entity,$context);
        if(!$this->isCsrfTokenValid('delete_'.$type.'_'.$id,(string)$request->request->get('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');
        $em->remove($entity);$em->flush();$this->addFlash('success','Az elem törölve.');
        return $this->redirectToRoute('tenant_admin_manage',['institutionSlug'=>$context->requireInstitution()->getSlug(),'type'=>$type]);
    }

    private function handle(string $type,TenantOwnedEntity $entity,Request $request,EntityManagerInterface $em,ContentWorkflow $workflow,bool $canEdit,bool $new):Response
    {
        $form=$this->form($type,$entity);$form->handleRequest($request);if($form->isSubmitted()&&$entity instanceof Service){$site=$entity->getSite();$building=$entity->getBuilding();$floor=$entity->getFloor();$room=$entity->getRoom();if($building&&(!$site||!$building->getSite()->getId()->equals($site->getId())))$form->get('building')->addError(new FormError('A kiválasztott épület nem ehhez a telephelyhez tartozik.'));if($floor&&(!$building||!$floor->getBuilding()->getId()->equals($building->getId())))$form->get('floor')->addError(new FormError('A kiválasztott szint nem ehhez az épülethez tartozik.'));if($room&&(!$floor||!$room->getFloor()->getId()->equals($floor->getId())))$form->get('room')->addError(new FormError('A kiválasztott helyiség nem ehhez a szinthez tartozik.'));}
        if($form->isSubmitted()&&$form->isValid()){if($entity instanceof ProcedureGuide){$lines=static fn(?string $v):array=>array_values(array_filter(array_map('trim',preg_split('/\R/',(string)$v)?:[])));$entity->setPreparation(['fasting'=>$form->get('fasting')->getData(),'hydration'=>(string)$form->get('hydration')->getData(),'medicationWarning'=>(string)$form->get('medicationWarning')->getData(),'steps'=>$lines($form->get('preparationText')->getData())])->setRequiredDocuments($lines($form->get('requiredDocumentsText')->getData()))->setAftercare(['steps'=>$lines($form->get('aftercareText')->getData()),'result'=>(string)$form->get('resultInformation')->getData(),'help'=>(string)$form->get('whenToSeekHelp')->getData()]);}$em->persist($entity);$em->flush();$this->addFlash('success',$new?'Az elem létrejött.':'A módosítások mentve.');return $this->redirectToRoute('tenant_admin_manage',['institutionSlug'=>$entity->getInstitution()->getSlug(),'type'=>$type]);}
        $user=$this->getUser();$revisions=$entity instanceof AbstractContent&&!$new?$em->getRepository(ContentRevision::class)->findBy(['contentType'=>$entity::class,'contentId'=>(string)$entity->getId()],['versionNumber'=>'DESC']):[];
        return $this->render('admin/content/form.html.twig',['form'=>$form,'type'=>$type,'isNew'=>$new,'entity'=>$entity,'institution'=>$entity->getInstitution(),'workflowTransitions'=>$entity instanceof AbstractContent&&$user instanceof User?$workflow->allowedTransitions($entity,$user):[],'revisions'=>$revisions,'canEditContent'=>$canEdit,'relatedSections'=>$new?[]:$this->relatedSections($type,$entity,$em)]);
    }

    private function prefillParent(string $type,TenantOwnedEntity $entity,Request $request,TenantContext $context,EntityManagerInterface $em):void
    {
        $parentType=$request->query->getString('parentType');$parentId=$request->query->getString('parent');if(!$parentType||!$parentId)return;$class=self::TYPES[$parentType]??null;if(!$class)return;$parent=$em->getRepository($class)->find($parentId);if(!$parent instanceof TenantOwnedEntity)return;$this->assertCurrentTenant($parent,$context);
        if($type==='building'&&$parent instanceof Site&&$entity instanceof Building)$entity->setSite($parent);
        elseif($type==='floor'&&$parent instanceof Building&&$entity instanceof Floor)$entity->setBuilding($parent);
        elseif($type==='room'&&$parent instanceof Floor&&$entity instanceof Room)$entity->setFloor($parent);
        elseif($type==='service'&&$entity instanceof Service){if($parent instanceof Department)$entity->setDepartment($parent);elseif($parent instanceof Room)$entity->setRoom($parent)->setFloor($parent->getFloor())->setBuilding($parent->getFloor()->getBuilding())->setSite($parent->getFloor()->getBuilding()->getSite());}
        elseif($type==='contact'&&$entity instanceof ContactPoint){if($parent instanceof Department)$entity->setDepartment($parent);elseif($parent instanceof Service)$entity->setService($parent);}
        elseif($type==='guide'&&$parent instanceof Service&&$entity instanceof ProcedureGuide)$entity->setService($parent);
    }

    private function relatedSections(string $type,TenantOwnedEntity $entity,EntityManagerInterface $em):array
    {
        $definitions=match($type){
            'site'=>[['type'=>'building','label'=>'Épületek','class'=>Building::class,'field'=>'site','title'=>'name','order'=>'name']],
            'building'=>[['type'=>'floor','label'=>'Emeletek és szintek','class'=>Floor::class,'field'=>'building','title'=>'name','order'=>'levelNumber']],
            'floor'=>[['type'=>'room','label'=>'Szobák és helyiségek','class'=>Room::class,'field'=>'floor','title'=>'name','order'=>'number']],
            'room'=>[['type'=>'service','label'=>'Itt elérhető ellátások','class'=>Service::class,'field'=>'room','title'=>'name','order'=>'name']],
            'department'=>[['type'=>'service','label'=>'Az osztály ellátásai','class'=>Service::class,'field'=>'department','title'=>'name','order'=>'name'],['type'=>'contact','label'=>'Kapcsolati pontok','class'=>ContactPoint::class,'field'=>'department','title'=>'label','order'=>'label']],
            'service'=>[['type'=>'guide','label'=>'Vizsgálati útmutatók','class'=>ProcedureGuide::class,'field'=>'service','title'=>'title','order'=>'title'],['type'=>'contact','label'=>'Kapcsolati pontok','class'=>ContactPoint::class,'field'=>'service','title'=>'label','order'=>'label']],
            default=>[],
        };
        $sections=[];foreach($definitions as $definition){$children=$em->getRepository($definition['class'])->findBy([$definition['field']=>$entity],[$definition['order']=>'ASC']);$items=[];foreach($children as $child){$meta=match($definition['type']){'floor'=>$child->getLevelNumber()!==null?'Szint: '.$child->getLevelNumber():null,'room'=>$child->getNumber(),'contact'=>$child->getValue(),'service'=>implode(' · ',array_filter([$child->getFloor()?->getName(),$child->getRoom()?->getNumber()])),default=>null};$getter='get'.ucfirst($definition['title']);$items[]=['id'=>$child->getId(),'title'=>$child->{$getter}(),'meta'=>$meta];}$sections[]=['type'=>$definition['type'],'label'=>$definition['label'],'items'=>$items];}
        return $sections;
    }

    private function form(string $type,TenantOwnedEntity $entity):FormInterface
    {
        $builder=$this->createFormBuilder($entity);
        if(in_array($type,['site','department','service','page','guide','journey','announcement'],true))$builder->add('slug',TextType::class,['label'=>'URL-azonosító']);
        $builder->add(in_array($type,['site','building','floor','room','department','service'],true)?'name':(in_array($type,['page','guide','journey','announcement'],true)?'title':'label'),TextType::class,['label'=>in_array($type,['page','guide','journey','announcement'],true)?'Cím':'Név']);
        if($entity instanceof Site)$builder->remove('title')->add('name')->add('address')->add('mapUrl')->add('accessibility',TextareaType::class,['required'=>false]);
        elseif($entity instanceof Building)$builder->add('site',EntityType::class,['class'=>Site::class,'choice_label'=>'name'])->add('code',TextType::class,['required'=>false,'label'=>'Épületkód']);
        elseif($entity instanceof Floor)$builder->add('building',EntityType::class,['class'=>Building::class,'choice_label'=>'name'])->add('levelNumber',IntegerType::class,['required'=>false,'label'=>'Szint száma']);
        elseif($entity instanceof Room)$builder->add('floor',EntityType::class,['class'=>Floor::class,'choice_label'=>'name'])->add('number',TextType::class,['required'=>false,'label'=>'Ajtó/terem száma']);
        elseif($entity instanceof Department)$builder->remove('title')->add('name')->add('summary',TextareaType::class,['required'=>false]);
        elseif($entity instanceof Service)$builder->remove('title')->add('name')->add('summary',TextareaType::class,['required'=>false,'label'=>'Rövid leírás'])->add('department',EntityType::class,['class'=>Department::class,'choice_label'=>'name','required'=>false,'label'=>'Osztály'])->add('site',EntityType::class,['class'=>Site::class,'choice_label'=>'name','required'=>false,'label'=>'Telephely'])->add('building',EntityType::class,['class'=>Building::class,'choice_label'=>static fn(Building $v):string=>$v->getSite()->getName().' – '.$v->getName(),'required'=>false,'label'=>'Épület'])->add('floor',EntityType::class,['class'=>Floor::class,'choice_label'=>static fn(Floor $v):string=>$v->getBuilding()->getName().' – '.$v->getName(),'required'=>false,'label'=>'Emelet / szint'])->add('room',EntityType::class,['class'=>Room::class,'choice_label'=>static fn(Room $v):string=>$v->getFloor()->getBuilding()->getName().' – '.$v->getFloor()->getName().' – '.$v->getName().($v->getNumber()?' ('.$v->getNumber().')':''),'required'=>false,'label'=>'Szoba / helyiség'])->add('locationDirections',TextareaType::class,['required'=>false,'label'=>'Odatalálási útmutató','help'=>'Például: a főbejárattól balra, a kék folyosón a második ajtó.']);
        elseif($entity instanceof ContactPoint)$builder->add('type',ChoiceType::class,['label'=>'Kapcsolat típusa','choices'=>['Telefon'=>'phone','E-mail'=>'email','Weboldal'=>'url','Személyes ügyintézés'=>'in_person']])->add('value',TextType::class,['label'=>'Elérhetőség'])->add('availability',TextareaType::class,['required'=>false,'label'=>'Elérhetőségi idő'])->add('department',EntityType::class,['class'=>Department::class,'choice_label'=>'name','required'=>false,'label'=>'Osztály'])->add('service',EntityType::class,['class'=>Service::class,'choice_label'=>'name','required'=>false,'label'=>'Ellátás']);
        elseif($entity instanceof InformationPage)$builder->add('summary',TextareaType::class,['required'=>false])->add('category',TextType::class,['required'=>false])->add('status',ChoiceType::class,['choices'=>array_combine(array_map(fn(ContentStatus $s)=>$s->value,ContentStatus::cases()),ContentStatus::cases())]);
        elseif($entity instanceof ProcedureGuide){$preparation=$entity->getPreparation();$aftercare=$entity->getAftercare();$builder->add('summary',TextareaType::class,['required'=>false,'label'=>'Rövid összefoglaló'])->add('purpose',TextareaType::class,['required'=>false,'label'=>'A vizsgálat célja'])->add('durationMinutes',IntegerType::class,['required'=>false,'label'=>'Időtartam percben'])->add('discomfort',TextareaType::class,['required'=>false,'label'=>'Fájdalom és kellemetlenség'])->add('service',EntityType::class,['class'=>Service::class,'choice_label'=>'name','required'=>false,'label'=>'Kapcsolódó ellátás'])->add('referralRequired',CheckboxType::class,['required'=>false,'label'=>'Beutaló szükséges'])->add('appointmentRequired',CheckboxType::class,['required'=>false,'label'=>'Előjegyzés szükséges'])->add('companionRequired',CheckboxType::class,['required'=>false,'label'=>'Kísérő szükséges'])->add('canDriveAfter',ChoiceType::class,['required'=>false,'label'=>'Lehet utána vezetni?','placeholder'=>'Nincs megadva','choices'=>['Igen'=>true,'Nem'=>false]])->add('fasting',ChoiceType::class,['mapped'=>false,'required'=>false,'label'=>'Éhgyomor szükséges?','placeholder'=>'Nincs megadva','choices'=>['Igen'=>true,'Nem'=>false],'data'=>$preparation['fasting']??null])->add('hydration',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Folyadékfogyasztási szabályok','data'=>$preparation['hydration']??''])->add('medicationWarning',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Gyógyszerekkel kapcsolatos figyelmeztetés','data'=>$preparation['medicationWarning']??''])->add('preparationText',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Felkészülési lépések (soronként)','data'=>implode("\n",$preparation['steps']??[])])->add('requiredDocumentsText',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Szükséges dokumentumok (soronként)','data'=>implode("\n",$entity->getRequiredDocuments())])->add('aftercareText',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Vizsgálat utáni teendők (soronként)','data'=>implode("\n",$aftercare['steps']??[])])->add('resultInformation',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Eredmény átvételének módja','data'=>$aftercare['result']??''])->add('whenToSeekHelp',TextareaType::class,['mapped'=>false,'required'=>false,'label'=>'Mikor kell segítséget kérni?','data'=>$aftercare['help']??''])->add('status',ChoiceType::class,['label'=>'Állapot','choices'=>array_combine(array_map(fn(ContentStatus $s)=>$s->value,ContentStatus::cases()),ContentStatus::cases())]);}
        elseif($entity instanceof PatientJourney)$builder->add('summary',TextareaType::class,['required'=>false,'label'=>'Rövid összefoglaló'])->add('targetAudience',TextType::class,['required'=>false,'label'=>'Célcsoport'])->add('status',ChoiceType::class,['label'=>'Állapot','choices'=>array_combine(array_map(fn(ContentStatus $s)=>$s->value,ContentStatus::cases()),ContentStatus::cases())]);
        elseif($entity instanceof Announcement)$builder->add('summary',TextareaType::class,['required'=>false])->add('body',TextareaType::class)->add('type',ChoiceType::class,['choices'=>['Normál'=>'normal','Figyelmeztetés'=>'warning','Sürgős'=>'urgent']])->add('priority',IntegerType::class)->add('status',ChoiceType::class,['choices'=>array_combine(array_map(fn(ContentStatus $s)=>$s->value,ContentStatus::cases()),ContentStatus::cases())]);
        if($entity instanceof AbstractContent)$builder->remove('status')->add('reviewDueAt',DateType::class,['required'=>false,'widget'=>'single_text','label'=>'Felülvizsgálati határidő']);return $builder->getForm();
    }
    private function denyUnlessEditor(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();}
    private function canEdit(TenantRoleChecker $roles):bool{$user=$this->getUser();return $user instanceof User&&(in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)||$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')||$roles->hasRole($user,'ROLE_EDITOR'));}
    private function denyUnlessContentViewer(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')&&!$roles->hasRole($user,'ROLE_MEDICAL_REVIEWER')&&!$roles->hasRole($user,'ROLE_PUBLISHER')&&!$roles->hasRole($user,'ROLE_AUDITOR')))throw $this->createAccessDeniedException();}
    private function assertCurrentTenant(TenantOwnedEntity $entity,TenantContext $context):void{if(!$entity->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();}
}
