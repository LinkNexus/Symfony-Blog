<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{slug}', name: 'app_profile', requirements: ["slug" => "^@[a-zA-Z0-9-]+$"])]
#[IsGranted("ROLE_USER")]
class ProfileController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: "/", name: "")]
    public function index(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user
    ): Response
    {
//        $user = $this->entityManager->getRepository(User::class)->findOneBy(["slug" => $slug]);

        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        return $this->render('user/profile.html.twig', [
            "user" => $user
        ]);
    }

    #[Route(path: "/about", name: "about")]
    public function about(
        #[MapEntity(mapping: ["slug" => "slug"])]
        ?User $user
    )
    {
        if (!$user) {
            return $this->redirectToRoute("app_home");
        }

        return $this->render('user/about.html.twig', [
            "user" => $user
        ]);
    }
}
