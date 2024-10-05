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

class AppController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    #[IsGranted('ROLE_USER')]
    #[Route('/current-user', name: 'current-user', methods: ["GET", 'POST'])]
    public function getCurrentUser(): JsonResponse
    {
        return new JsonResponse($this->getUser());
    }

    #[IsGranted('ROLE_USER', message: "You need to be logged-in before accessing this page")]
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();

//        if (!$user->isVerified()) {
//            return $this->redirectToRoute('app_logout');
//        }

        $posts = $this->entityManager->getRepository(Post::class)->findAllAccessiblePosts();
        $commentForm = $this->createForm(CommentFormType::class);

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
            "onHomePage" => true,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete/file', name: 'app_delete_files', methods: ['POST'])]
    public function deleteFiles(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $fileSystem = new Filesystem();
        $path = $this->getParameter('kernel.project_dir') . '/public' . $data['url'];
        $fileSystem->remove($path);

        return new JsonResponse(['message' => 'File deleted!']);
    }

    #[Route('/post/{id}/preview', name: 'app_post_preview')]
    public function postPreview(?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        return $this->render("app/post_preview.html.twig", [
            "post" => $post
        ]);
    }

    /* #[Route(path: "/adminer", name: "app_adminer")]
    public function adminer(): Response
    {
        ob_start();
        include "../public/adminer.php";
        $content = ob_get_clean();

        return new Response($content);
    } */
}
