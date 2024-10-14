<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UploadAvatarFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;

#[Route('/{slug}', name: 'app_profile', requirements: ["slug" => "^@[a-zA-Z0-9-]+$"])]
#[IsGranted("ROLE_USER")]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private Packages $assets,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    )
    {}

    #[Route(path: "/", name: "")]
    public function index(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user,
        Request $request,
        CropperInterface $cropper,
    ): Response
    {
//        $user = $this->entityManager->getRepository(User::class)->findOneBy(["slug" => $slug]);

        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        $uploadAvatarForm = $this->createForm(UploadAvatarFormType::class, $user);
        $uploadAvatarForm->handleRequest($request);

        $cropForm = null;

        if ($uploadAvatarForm->isSubmitted() && $uploadAvatarForm->isValid()) {
            $this->entityManager->flush();
        }

        return $this->render('user/profile.html.twig', [
            "user" => $user,
            'uploadForm' => $uploadAvatarForm,
        ]);
    }

    #[Route(path: "/about", name: "about")]
    public function about(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user
    ): Response
    {
        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        return $this->render('user/about.html.twig', [
            "user" => $user
        ]);
    }
}
