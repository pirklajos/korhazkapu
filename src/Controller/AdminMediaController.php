<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\MediaAsset;
use App\Media\MediaUploader;
use App\Security\TenantRoleChecker;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

#[IsGranted('ROLE_USER')]
final class AdminMediaController extends AbstractController
{
    #[Route('/i/{institutionSlug}/admin/media',name:'tenant_admin_media',methods:['GET','POST'])]
    public function __invoke(Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em,MediaUploader $uploader):Response
    {
        $user=$this->getUser();if(!$user instanceof \App\Entity\User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();
        $form=$this->createFormBuilder()->add('file',FileType::class,['constraints'=>[new Assert\NotNull(),new Assert\File(maxSize:'5M',mimeTypes:['image/jpeg','image/png','image/webp','application/pdf'])]])->add('altText',TextType::class,['constraints'=>[new Assert\NotBlank(),new Assert\Length(max:500)]])->getForm();$form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){try{$asset=$uploader->upload($form->get('file')->getData(),$form->get('altText')->getData(),$context->requireInstitution());$em->persist($asset);$em->flush();$this->addFlash('success','A médiafájl feltöltve.');return $this->redirectToRoute('tenant_admin_media',['institutionSlug'=>$context->requireInstitution()->getSlug()]);}catch(\InvalidArgumentException $e){$form->get('file')->addError(new FormError($e->getMessage()));}}
        return $this->render('admin/media/index.html.twig',['form'=>$form,'institution'=>$context->requireInstitution(),'assets'=>$em->getRepository(MediaAsset::class)->findBy(['institution'=>$context->requireInstitution()],['createdAt'=>'DESC'])]);
    }
    #[Route('/i/{institutionSlug}/admin/media/{id}',name:'tenant_admin_media_view',methods:['GET'])]
    public function view(string $id,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em,MediaUploader $uploader):BinaryFileResponse
    {
        $this->denyUnlessEditor($roles);$asset=$em->getRepository(MediaAsset::class)->find($id)??throw $this->createNotFoundException();$this->assertTenant($asset,$context);
        try{$path=$uploader->path($asset);}catch(\RuntimeException){throw $this->createNotFoundException();}
        $response=new BinaryFileResponse($path);$response->headers->set('Content-Type',$asset->getMimeType());$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$asset->getOriginalName());$response->setPrivate();$response->setMaxAge(300);return $response;
    }
    #[Route('/i/{institutionSlug}/admin/media/{id}/delete',name:'tenant_admin_media_delete',methods:['POST'])]
    public function delete(string $id,Request $request,TenantContext $context,TenantRoleChecker $roles,EntityManagerInterface $em,MediaUploader $uploader):Response
    {
        $this->denyUnlessEditor($roles);$asset=$em->getRepository(MediaAsset::class)->find($id)??throw $this->createNotFoundException();$this->assertTenant($asset,$context);
        if(!$this->isCsrfTokenValid('delete_media_'.$id,(string)$request->request->get('_token')))throw $this->createAccessDeniedException('Érvénytelen CSRF token.');
        $uploader->delete($asset);$em->remove($asset);$em->flush();$this->addFlash('success','A médiafájl törölve.');return $this->redirectToRoute('tenant_admin_media',['institutionSlug'=>$context->requireInstitution()->getSlug()]);
    }
    private function denyUnlessEditor(TenantRoleChecker $roles):void{$user=$this->getUser();if(!$user instanceof \App\Entity\User||(!in_array('ROLE_PLATFORM_ADMIN',$user->getRoles(),true)&&!$roles->hasRole($user,'ROLE_INSTITUTION_ADMIN')&&!$roles->hasRole($user,'ROLE_EDITOR')))throw $this->createAccessDeniedException();}
    private function assertTenant(MediaAsset $asset,TenantContext $context):void{if(!$asset->getInstitution()->getId()->equals($context->requireInstitution()->getId()))throw $this->createAccessDeniedException();}
}
