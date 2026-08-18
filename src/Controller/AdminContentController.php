<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Building;
use App\Entity\ContactPoint;
use App\Entity\Department;
use App\Entity\InformationPage;
use App\Entity\Institution;
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
    private const TYPES = [
        'site'=>['class'=>Site::class,'label'=>'Telephelyek','group'=>'structure'],'building'=>['class'=>Building::class,'label'=>'Épületek','group'=>'structure'],'floor'=>['class'=>Floor::class,'label'=>'Szintek','group'=>'structure'],'room'=>['class'=>Room::class,'label'=>'Helyiségek','group'=>'structure'],'department'=>['class'=>Department::class,'label'=>'Osztályok','group'=>'structure'],'service'=>['class'=>Service::class,'label'=>'Ellátások','group'=>'structure'],'contact'=>['class'=>ContactPoint::class,'label'=>'Kapcsolati pontok','group'=>'structure'],
        'page'=>['class'=>InformationPage::class,'label'=>'Tájékoztatók','group'=>'content'],'guide'=>['class'=>ProcedureGuide::class,'label'=>'Vizsgálati útmutatók','group'=>'content'],'journey'=>['class'=>PatientJourney::class,'label'=>'Betegutak','group'=>'content'],'announcement'=>['class'=>Announcement::class,'label'=>'Közlemények','group'=>'content'],
    ];
    #[Route('/admin/content',name:'admin_content',methods:['GET'])]
    #[Route('/i/{institutionSlug}/admin/content',name:'tenant_admin_content',methods:['GET'])]
    public function __invoke(TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $user=$this->getUser(); $institution=$context->requireInstitution();
        if (!$user instanceof User || (!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true) && !$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN') && !$roles->hasRole($user,'ROLE_EDITOR') && !$roles->hasRole($user,'ROLE_AUDITOR'))) throw $this->createAccessDeniedException();
        return $this->overview('content',$institution,$em);
    }

    #[Route('/i/{institutionSlug}/admin/structure',name:'tenant_admin_structure',methods:['GET'])]
    public function structure(TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response{$institution=$context->requireInstitution();$this->deny($roles);return $this->overview('structure',$institution,$em);}

    #[Route('/i/{institutionSlug}/admin/manage/{type}',name:'tenant_admin_manage',methods:['GET'])]
    public function manage(string $type,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em):Response
    {
        $institution=$context->requireInstitution();$this->deny($roles);$config=self::TYPES[$type]??throw $this->createNotFoundException();$entities=$em->getRepository($config['class'])->findBy(['institution'=>$institution],[$this->orderField($type)=>'ASC']);$items=[];foreach($entities as $entity)$items[]=$this->item($type,$entity);
        return $this->render('admin/content/manage.html.twig',['institution'=>$institution,'type'=>$type,'config'=>$config,'items'=>$items]);
    }

    private function overview(string $group,Institution $institution,EntityManagerInterface $em):Response
    {
        $cards=[];foreach(self::TYPES as $type=>$config){if($config['group']!==$group)continue;$repository=$em->getRepository($config['class']);$entities=$repository->findBy(['institution'=>$institution],[$this->orderField($type)=>'ASC'],6);$cards[]=['type'=>$type,'label'=>$config['label'],'count'=>$repository->count(['institution'=>$institution]),'items'=>array_map(fn(object $entity):array=>$this->item($type,$entity),$entities)];}
        return $this->render('admin/content/index.html.twig',['institution'=>$institution,'group'=>$group,'cards'=>$cards]);
    }

    private function item(string $type,object $entity):array
    {
        $title=match($type){'page','guide','journey','announcement'=>$entity->getTitle(),'contact'=>$entity->getLabel(),default=>$entity->getName()};
        $meta=match($type){'building'=>$entity->getSite()->getName(),'floor'=>$entity->getBuilding()->getName(),'room'=>trim(($entity->getNumber()??'').' · '.$entity->getFloor()->getBuilding()->getName().' / '.$entity->getFloor()->getName(),' ·'),'service'=>implode(' · ',array_filter([$entity->getBuilding()?->getName(),$entity->getFloor()?->getName(),$entity->getRoom()?->getNumber()])),'contact'=>$entity->getValue(),default=>null};
        return ['id'=>$entity->getId(),'title'=>$title,'meta'=>$meta,'status'=>method_exists($entity,'getStatus')?$entity->getStatus()->value:null];
    }

    private function orderField(string $type):string{return match($type){'page','guide','journey','announcement'=>'title','contact'=>'label','room'=>'number',default=>'name'};}

    private function deny(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')&&!$roles->hasRole($user,'ROLE_AUDITOR')))throw $this->createAccessDeniedException();}
}
