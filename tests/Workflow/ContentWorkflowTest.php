<?php
declare(strict_types=1);
namespace App\Tests\Workflow;
use App\Entity\ContentStatus;
use App\Entity\InformationPage;
use App\Entity\Institution;
use App\Entity\Membership;
use App\Entity\User;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use App\Workflow\ContentWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ContentWorkflowTest extends TestCase
{
    public function testEditorCanSubmitDraftForMedicalReview():void
    {
        [$workflow,$content,$user]=$this->setupWorkflow('ROLE_EDITOR');self::assertSame([ContentStatus::MedicalReview],$workflow->allowedTransitions($content,$user));
    }
    public function testReturnToDraftRequiresComment():void
    {
        [$workflow,$content,$user]=$this->setupWorkflow('ROLE_MEDICAL_REVIEWER');$content->setStatus(ContentStatus::MedicalReview);$this->expectException(\DomainException::class);$workflow->transition($content,ContentStatus::Draft,$user,'');
    }
    /** @return array{ContentWorkflow,InformationPage,User} */
    private function setupWorkflow(string $role):array
    {
        $institution=new Institution('Teszt','TK','teszt','Budapest');$context=new TenantContext();$context->setInstitution($institution);$user=new User('user@example.test','Tesztelő');new Membership($user,$institution,[$role]);$em=$this->createMock(EntityManagerInterface::class);return [new ContentWorkflow($em,new TenantRoleChecker($context)),new InformationPage($institution,'Tájékoztató','tajekoztato'),$user];
    }
}
