<?php
declare(strict_types=1);
namespace App\Controller;
use App\Search\PublicSearch;
use App\Tenant\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class SearchController extends AbstractController
{
    #[Route('/i/{institutionSlug}/kereses',name:'tenant_search',methods:['GET'])]
    public function __invoke(Request $request,TenantContext $context,PublicSearch $search):Response{$query=trim($request->query->getString('q'));return $this->render('public/search.html.twig',['institution'=>$context->requireInstitution(),'query'=>$query,'results'=>$search->search($context->requireInstitution(),$query)]);}
}
