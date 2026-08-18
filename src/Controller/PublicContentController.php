<?php
declare(strict_types=1);
namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Building;
use App\Entity\ContactPoint;
use App\Entity\Department;
use App\Entity\InformationPage;
use App\Entity\ProcedureGuide;
use App\Entity\Service;
use App\Entity\Site;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/i/{institutionSlug}',requirements:['institutionSlug'=>'[a-z0-9]+(?:-[a-z0-9]+)*'])]
final class PublicContentController extends AbstractController
{
    #[Route('/telephelyek',name:'tenant_sites',methods:['GET'])]
    public function sites(TenantContext $context,EntityManagerInterface $em):Response{return $this->render('public/list.html.twig',$this->base($context)+['kind'=>'sites','title'=>'Telephelyek','intro'=>'Címek, megközelítés és akadálymentességi információk.','items'=>$em->getRepository(Site::class)->findBy(['institution'=>$context->requireInstitution(),'active'=>true],['name'=>'ASC'])]);}
    #[Route('/telephelyek/{slug}',name:'tenant_site_show',methods:['GET'])]
    public function site(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$site=$this->find(Site::class,$slug,$context,$em);return $this->render('public/site.html.twig',$this->base($context)+['site'=>$site,'buildings'=>$em->getRepository(Building::class)->findBy(['institution'=>$context->requireInstitution(),'site'=>$site],['name'=>'ASC']),'services'=>$em->getRepository(Service::class)->findBy(['institution'=>$context->requireInstitution(),'site'=>$site],['name'=>'ASC'])]);}
    #[Route('/ellatasok',name:'tenant_services',methods:['GET'])]
    public function services(TenantContext $context,EntityManagerInterface $em):Response{return $this->render('public/list.html.twig',$this->base($context)+['kind'=>'services','title'=>'Ellátások és vizsgálatok','intro'=>'Keresse meg az Önnek szükséges szakrendelést, vizsgálatot vagy szolgáltatást.','items'=>$em->getRepository(Service::class)->findBy(['institution'=>$context->requireInstitution()],['name'=>'ASC'])]);}
    #[Route('/ellatasok/{slug}',name:'tenant_service_show',methods:['GET'])]
    public function service(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$service=$this->find(Service::class,$slug,$context,$em);$guides=array_values(array_filter($em->getRepository(ProcedureGuide::class)->findBy(['institution'=>$context->requireInstitution(),'service'=>$service]),fn(ProcedureGuide $v)=>$v->isPubliclyVisible()));return $this->render('public/service.html.twig',$this->base($context)+['service'=>$service,'guides'=>$guides,'contacts'=>$em->getRepository(ContactPoint::class)->findBy(['institution'=>$context->requireInstitution(),'service'=>$service])]);}
    #[Route('/osztalyok/{slug}',name:'tenant_department_show',methods:['GET'])]
    public function department(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$department=$this->find(Department::class,$slug,$context,$em);return $this->render('public/department.html.twig',$this->base($context)+['department'=>$department,'services'=>$em->getRepository(Service::class)->findBy(['institution'=>$context->requireInstitution(),'department'=>$department],['name'=>'ASC']),'contacts'=>$em->getRepository(ContactPoint::class)->findBy(['institution'=>$context->requireInstitution(),'department'=>$department])]);}
    #[Route('/vizsgalati-utmutatok/{slug}',name:'tenant_guide_show',methods:['GET'])]
    public function guide(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$guide=$this->findPublic(ProcedureGuide::class,$slug,$context,$em);return $this->render('public/guide.html.twig',$this->base($context)+['guide'=>$guide]);}
    #[Route('/tajekoztatok/{slug}',name:'tenant_page_show',methods:['GET'])]
    public function page(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$page=$this->findPublic(InformationPage::class,$slug,$context,$em);return $this->render('public/page.html.twig',$this->base($context)+['page'=>$page]);}
    #[Route('/kozlemenyek',name:'tenant_announcements',methods:['GET'])]
    public function announcements(TenantContext $context,EntityManagerInterface $em):Response{$items=array_values(array_filter($em->getRepository(Announcement::class)->findBy(['institution'=>$context->requireInstitution()],['priority'=>'DESC']),fn(Announcement $v)=>$v->isPubliclyVisible()));return $this->render('public/list.html.twig',$this->base($context)+['kind'=>'announcements','title'=>'Közlemények','intro'=>'Aktuális intézményi hírek, változások és fontos figyelmeztetések.','items'=>$items]);}
    #[Route('/kozlemenyek/{slug}',name:'tenant_announcement_show',methods:['GET'])]
    public function announcement(string $slug,TenantContext $context,EntityManagerInterface $em):Response{$announcement=$this->findPublic(Announcement::class,$slug,$context,$em);return $this->render('public/announcement.html.twig',$this->base($context)+['announcement'=>$announcement]);}
    #[Route('/kapcsolat',name:'tenant_contacts',methods:['GET'])]
    public function contacts(TenantContext $context,EntityManagerInterface $em):Response{return $this->render('public/contacts.html.twig',$this->base($context)+['contacts'=>$em->getRepository(ContactPoint::class)->findBy(['institution'=>$context->requireInstitution()],['label'=>'ASC'])]);}
    /** @return array{institution:\App\Entity\Institution} */ private function base(TenantContext $context):array{return ['institution'=>$context->requireInstitution()];}
    /** @template T of object @param class-string<T> $class @return T */ private function find(string $class,string $slug,TenantContext $context,EntityManagerInterface $em):object{return $em->getRepository($class)->findOneBy(['institution'=>$context->requireInstitution(),'slug'=>$slug])??throw $this->createNotFoundException();}
    /** @template T of \App\Entity\AbstractContent @param class-string<T> $class @return T */ private function findPublic(string $class,string $slug,TenantContext $context,EntityManagerInterface $em):object{$content=$this->find($class,$slug,$context,$em);if(!$content->isPubliclyVisible())throw $this->createNotFoundException();return $content;}
}
