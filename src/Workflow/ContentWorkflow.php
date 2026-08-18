<?php
declare(strict_types=1);
namespace App\Workflow;

use App\Entity\AbstractContent;
use App\Entity\Announcement;
use App\Entity\AuditLog;
use App\Entity\ContentRevision;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\PatientJourney;
use App\Entity\ProcedureGuide;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ContentWorkflow
{
    public function __construct(private EntityManagerInterface $em,private TenantRoleChecker $roles){}
    /** @return list<ContentStatus> */
    public function allowedTransitions(AbstractContent $content,User $user):array
    {
        $status=$content->getStatus();$editor=$this->roles->hasRole($user,'ROLE_EDITOR')||$this->roles->hasRole($user,'ROLE_INSTITUTION_ADMIN');$medical=$this->roles->hasRole($user,'ROLE_MEDICAL_REVIEWER');$publisher=$this->roles->hasRole($user,'ROLE_PUBLISHER')||$this->roles->hasRole($user,'ROLE_INSTITUTION_ADMIN');
        return match($status){ContentStatus::Draft=>$editor?[ContentStatus::MedicalReview]:[],ContentStatus::MedicalReview=>$medical?[ContentStatus::CommunicationReview,ContentStatus::Draft]:[],ContentStatus::CommunicationReview=>$publisher?[ContentStatus::Approved,ContentStatus::Draft]:[],ContentStatus::Approved=>$publisher?[ContentStatus::Published,ContentStatus::Draft]:[],ContentStatus::Published=>$publisher?[ContentStatus::Archived]:[],ContentStatus::Expired=>$editor?[ContentStatus::Draft]:[],ContentStatus::Archived=>[]};
    }
    public function transition(AbstractContent $content,ContentStatus $target,User $user,?string $comment):void
    {
        if(!in_array($target,$this->allowedTransitions($content,$user),true))throw new \DomainException('Ez az állapotváltás nem engedélyezett.');$from=$content->getStatus();if($target===ContentStatus::Draft&&trim((string)$comment)==='')throw new \DomainException('Visszaküldéskor kötelező megjegyzést írni.');
        if($target===ContentStatus::Published)$this->createRevision($content,$user,'Publikálás előtti automatikus verzió');$content->setStatus($target);$this->em->persist(new AuditLog($content->getInstitution(),'content_status_changed',$content::class,(string)$content->getId(),['from'=>$from->value,'to'=>$target->value,'comment'=>trim((string)$comment)],$user));$this->em->flush();
    }
    public function createRevision(AbstractContent $content,User $user,?string $summary):ContentRevision
    {
        $latest=$this->em->getRepository(ContentRevision::class)->findOneBy(['contentType'=>$content::class,'contentId'=>(string)$content->getId()],['versionNumber'=>'DESC']);$revision=new ContentRevision($content->getInstitution(),$content::class,(string)$content->getId(),($latest?->getVersionNumber()??0)+1,$this->snapshot($content),$summary,$user);$this->em->persist($revision);return $revision;
    }
    public function restore(AbstractContent $content,ContentRevision $revision,User $user):void
    {
        if($revision->getContentType()!==$content::class||$revision->getContentId()!==(string)$content->getId())throw new \DomainException('A verzió nem ehhez a tartalomhoz tartozik.');$this->createRevision($content,$user,'Visszaállítás előtti automatikus verzió');$data=$revision->getSnapshot();$content->setTitle((string)$data['title'])->setSlug((string)$data['slug'])->setSummary($data['summary']??null)->setStatus(ContentStatus::Draft)->setReviewDueAt(isset($data['reviewDueAt'])&&$data['reviewDueAt']?new \DateTimeImmutable($data['reviewDueAt']):null);
        if($content instanceof InformationPage){$content->setCategory($data['category']??null)->setTags($data['tags']??[])->setContent($data['content']??[]);}elseif($content instanceof Announcement){$content->setType($data['type']??'normal')->setBody($data['body']??'')->setPriority((int)($data['priority']??0));}elseif($content instanceof PatientJourney)$content->setTargetAudience($data['targetAudience']??null);elseif($content instanceof ProcedureGuide)$content->setPurpose($data['purpose']??null)->setDurationMinutes($data['durationMinutes']??null)->setDiscomfort($data['discomfort']??null)->setPreparation($data['preparation']??[])->setRequiredDocuments($data['requiredDocuments']??[])->setAftercare($data['aftercare']??[]);
        $this->em->persist(new AuditLog($content->getInstitution(),'content_revision_restored',$content::class,(string)$content->getId(),['version'=>$revision->getVersionNumber()],$user));$this->em->flush();
    }
    /** @return array<string,mixed> */
    private function snapshot(AbstractContent $content):array
    {
        $data=['title'=>$content->getTitle(),'slug'=>$content->getSlug(),'summary'=>$content->getSummary(),'status'=>$content->getStatus()->value,'reviewDueAt'=>$content->getReviewDueAt()?->format('Y-m-d')];
        if($content instanceof InformationPage)$data+=['category'=>$content->getCategory(),'tags'=>$content->getTags(),'content'=>$content->getContent()];elseif($content instanceof Announcement)$data+=['type'=>$content->getType(),'body'=>$content->getBody(),'priority'=>$content->getPriority()];elseif($content instanceof PatientJourney)$data+=['targetAudience'=>$content->getTargetAudience()];elseif($content instanceof ProcedureGuide)$data+=['purpose'=>$content->getPurpose(),'durationMinutes'=>$content->getDurationMinutes(),'discomfort'=>$content->getDiscomfort(),'preparation'=>$content->getPreparation(),'requiredDocuments'=>$content->getRequiredDocuments(),'aftercare'=>$content->getAftercare()];return $data;
    }
}
