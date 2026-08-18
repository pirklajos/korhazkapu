<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\Feedback;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
final class FeedbackController extends AbstractController
{
    #[Route('/i/{institutionSlug}/visszajelzes',name:'tenant_feedback',methods:['POST'])]
    public function __invoke(Request $request,TenantContext $context,EntityManagerInterface $em,#[Autowire(service:'limiter.feedback')] RateLimiterFactory $feedbackLimiter):Response{$institution=$context->requireInstitution();$limit=$feedbackLimiter->create(($request->getClientIp()??'unknown').'|'.$institution->getSlug())->consume();if(!$limit->isAccepted())throw new TooManyRequestsHttpException($limit->getRetryAfter()->getTimestamp()-time(),'Túl sok visszajelzés érkezett. Kérjük, próbálja később.');$path=$request->request->getString('page');if(!str_starts_with($path,'/i/'.$institution->getSlug().'/')&&$path!=='/i/'.$institution->getSlug())$path='/i/'.$institution->getSlug();if(!$this->isCsrfTokenValid('feedback_'.$path,$request->request->getString('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');$helpful=$request->request->getString('helpful');if(!in_array($helpful,['yes','no'],true))throw $this->createNotFoundException();$comment=mb_substr($request->request->getString('comment'),0,2000);$em->persist(new Feedback($institution,$path,$helpful==='yes',$comment?:null));$em->flush();$this->addFlash('feedback_success','Köszönjük a visszajelzést!');return $this->redirect($path.'#page-feedback');}
}
