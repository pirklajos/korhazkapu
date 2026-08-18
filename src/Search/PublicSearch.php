<?php
declare(strict_types=1);
namespace App\Search;
use App\Entity\Announcement;
use App\Entity\Department;
use App\Entity\InformationPage;
use App\Entity\Institution;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\SearchSynonym;
use App\Entity\Service;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class PublicSearch
{
    public function __construct(private EntityManagerInterface $em,private UrlGeneratorInterface $urls){}
    /** @return list<array{type:string,title:string,summary:string,url:string}> */
    public function search(Institution $institution,string $query):array
    {
        $needle=$this->normalize($query);if(mb_strlen($needle)<2)return [];$terms=[$needle];foreach($this->em->getRepository(SearchSynonym::class)->findBy(['institution'=>$institution]) as $synonym){$group=array_map($this->normalize(...),[$synonym->getTerm(),...$synonym->getSynonyms()]);$matches=false;foreach($group as $candidate)if(str_contains($needle,$candidate)||str_contains($candidate,$needle)){$matches=true;break;}if($matches)$terms=array_values(array_unique([...$terms,...$group]));}
        $results=[];$add=function(string $type,string $title,?string $summary,string $url)use(&$results,$terms):void{$haystack=$this->normalize($title.' '.($summary??''));$score=0;foreach($terms as $term){if(str_contains($this->normalize($title),$term))$score+=3;if(str_contains($haystack,$term))$score++;}if($score)$results[]=['type'=>$type,'title'=>$title,'summary'=>$summary??'','url'=>$url,'score'=>$score];};$slug=$institution->getSlug();
        foreach($this->em->getRepository(Service::class)->findBy(['institution'=>$institution]) as $v)$add('Ellátás',$v->getName(),$v->getSummary(),$this->urls->generate('tenant_service_show',['institutionSlug'=>$slug,'slug'=>$v->getSlug()]));
        foreach($this->em->getRepository(Department::class)->findBy(['institution'=>$institution]) as $v)$add('Osztály',$v->getName(),$v->getSummary(),$this->urls->generate('tenant_department_show',['institutionSlug'=>$slug,'slug'=>$v->getSlug()]));
        foreach($this->em->getRepository(Site::class)->findBy(['institution'=>$institution,'active'=>true]) as $v)$add('Telephely',$v->getName(),$v->getAddress(),$this->urls->generate('tenant_site_show',['institutionSlug'=>$slug,'slug'=>$v->getSlug()]));
        foreach([[InformationPage::class,'Tájékoztató','tenant_page_show'],[ProcedureGuide::class,'Vizsgálati útmutató','tenant_guide_show'],[PatientJourney::class,'Betegút','tenant_journey_show'],[Announcement::class,'Közlemény','tenant_announcement_show']] as [$class,$type,$route])foreach($this->em->getRepository($class)->findBy(['institution'=>$institution]) as $v)if($v->isPubliclyVisible())$add($type,$v->getTitle(),$v->getSummary(),$this->urls->generate($route,['institutionSlug'=>$slug,'slug'=>$v->getSlug()]));
        usort($results,fn(array $a,array $b)=>$b['score']<=>$a['score']);return array_map(function(array $v){unset($v['score']);return $v;},$results);
    }
    private function normalize(string $value):string{$value=mb_strtolower(trim($value));$ascii=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value);return preg_replace('/[^a-z0-9]+/',' ',is_string($ascii)?$ascii:$value)??$value;}
}
