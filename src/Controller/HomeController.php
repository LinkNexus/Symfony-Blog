<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    #[Route('/current-user', name: 'current-user', methods: ["GET", 'POST'])]
    public function getCurrentUser(): JsonResponse
    {
        return new JsonResponse($this->getUser());
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        $posts = $this->entityManager->getRepository(Post::class)->findAllAccessiblePosts();
        $commentForm = $this->createForm(CommentFormType::class);

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
            "onHomePage" => true,
        ]);
    }

    #[Route('/delete/file', name: 'app_delete_files', methods: ['POST'])]
    public function deleteFiles(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $fileSystem = new Filesystem();
        $path = $this->getParameter('kernel.project_dir') . '/public' . $data['url'];
        $fileSystem->remove($path);

        return new JsonResponse(['message' => 'File deleted!']);
    }
}
