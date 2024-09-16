<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostModification;
use App\Entity\PostReaction;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Form\PostType;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/post', name: 'app_post_')]
#[IsGranted('ROLE_USER')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploader $fileUploader
    )
    {}

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();

        $post = new Post();
        $createPostForm = $this->createForm(PostType::class, $post);
        $createPostForm->handleRequest($request);

        if ($createPostForm->isSubmitted()  && $createPostForm->isValid()) {
            $post->setOwner($user);

            try {
                if ($user->getBlockedTill() >= new \DateTimeImmutable()) {
                    $this->addFlash(
                        "danger",
                        "Your profile has been blocked. You can neither post nor comment for " . $user->getBlockedTill()->diff(new \DateTimeImmutable())->days . " days"
                    );
                    return $this->redirectToRoute('app_home');
                }

                $this->addFlash('success', 'Your Post was successfully uploaded!');
                $this->entityManager->persist($post);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_post_show', ["id" => $post->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to update the entity: ' . $e->getMessage());
            }
        }

        return $this->render('post/create.html.twig', [
            'user' => $user,
            'form' => $createPostForm,
        ]);
    }

    #[Route('/{id}/', name: 'show', requirements: ["page" => "\d+"], methods: ['GET', "POST"])]
    public function show(
        ?Post $post,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^[\w-]+$/'])] ?string $filter
    ): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        foreach ($post->getOwner()->getBlockedUsers() as $block) {
            if ($block->getBlockedUser() === $this->getUser()) {
                return $this->redirectToRoute('app_home');
            }
        }

        $allReplies = [];
        $comments = match ($filter) {
            "oldest" => $this->entityManager->getRepository(Comment::class)
                ->findBy([
                    "post" => $post,
                    "respondedComment" => null
                ]),

            "more-reactions" => $this->entityManager->getRepository(Comment::class)
                ->findByReactionsNumber($post, "DESC", $this->entityManager),

            "less-reactions" => $this->entityManager->getRepository(Comment::class)
                ->findByReactionsNumber($post, "ASC", $this->entityManager),

            default => $this->entityManager->getRepository(Comment::class)
                ->findBy([
                    "post" => $post,
                    "respondedComment" => null
                ], ["createdAt" => "DESC"]),
        };

        $comments = new ArrayCollection($comments);

        foreach ($comments as $comment) {

            $replies = [];

            foreach ($comment->getReplies() as $reply) {
                $replies[] = [
                    "comment" => $reply,
                    "status" => "primary"
                ];

                foreach ($reply->getReplies() as $secondaryReply) {
                    $replies[] = [
                        "comment" => $secondaryReply,
                        "status" => "secondary"
                    ];

                    foreach ($secondaryReply->getReplies() as $otherReply) {
                        $replies[] = [
                            "comment" => $otherReply,
                            "status" => "secondary"
                        ];

                        foreach ($this->getAllReplies($otherReply) as $value) {
                            $replies[] = [
                                "comment" => $value,
                                "status" => "secondary"
                            ];
                        }
                    }
                }
            }

            $allReplies[strval($comment->getId())] = $replies;
        }

        $allReplies = new ArrayCollection($allReplies);

        foreach ($comments as $comment) {
            foreach ($comment->getOwner()->getBlockedUsers() as $block) {
                if ($block->getBlockedUser() === $this->getUser()) {
                    $comments->removeElement($comment);
                    $allReplies->remove(strval($comment->getId()));
                }
            }
        }

        foreach ($allReplies as $replies) {
            foreach ($replies as $reply) {
                foreach ($reply["comment"]->getOwner()->getBlockedUsers() as $block) {
                    if ($block->getBlockedUser() === $this->getUser()) {
                        $replies->removeElement($reply);
                    }
                }
            }
        }

        // dd($this->getUser()->getBlockedUsers(), $this->getUser()->getBlocks());

        return $this->render('post/show.html.twig', [
            'posts' => [$post],
            'user' => $this->getUser(),
            "allReplies" => $allReplies,
            "comments" => $comments,
            "onHomePage" => false
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        if ($post->getOwner() !== $this->getUser()) {
            $this->addFlash('danger', 'You cannot modify a post that is not yours');
            return $this->redirectToRoute('app_home');
        }

        $currentContent = $post->getContent();
        $restrictedUsers = $post->getPostAudience()?->getUsers();
        $updatePostForm = $this->createForm(PostType::class, $post, [
            'users' => $restrictedUsers
        ]);
        $updatePostForm->handleRequest($request);

        if ($updatePostForm->isSubmitted() && $updatePostForm->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());

            if ($post->getContent() !== $currentContent) {
                $postModification = new PostModification();
                $postModification->setPost($post);
                $postModification->setContent($currentContent);
                $post->addPostModification($postModification);
                $this->entityManager->persist($postModification);
            }

            try {
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to update the entity: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Your Post was successfully updated!');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'user' => $this->getUser(),
            'form' => $updatePostForm
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    public function delete(?Post $post, MailerInterface $mailer): Response
    {
        $user = $this->getUser();

        if (!$post) {
            return new JsonResponse(["message" => "The requested Post is not found"], 404);
        }

        if (!in_array("ROLE_ADMIN", $user->getRoles()) && $user !== $post->getOwner()) {
            return new JsonResponse(["message" => "You cannot delete this post"]);
        }

        $postReactions = $post->getPostReactions();
        $postAudience = $post->getPostAudience();
        $postModifications = $post->getPostModifications();
        $comments = $post->getComments();
        $commentReactions = [];

        if (!empty($comments->toArray())) {
            foreach ($comments as $comment) {
                $commentReactions = array_merge($commentReactions, $comment->getCommentReactions()->toArray());
                $this->entityManager->remove($comment);
            }
        }

        if (!empty($commentReactions)) {
            foreach ($commentReactions as $commentReaction) {
                $this->entityManager->remove($commentReaction);
            }
        }

        if (!empty($postReactions->toArray())) {
            foreach ($postReactions as $reaction) {
                $this->entityManager->remove($reaction);
            }
        }

        if (!empty($postModifications->toArray())) {
            foreach ($postModifications as $modification) {
                $this->entityManager->remove($modification);
            }
        }

        if ($postAudience) {
            $this->entityManager->remove($postAudience);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $templatedEmail = (new TemplatedEmail())
                ->htmlTemplate("mail/delete_feedback.html.twig")
                ->to($post->getOwner()->getEmail())
                ->from(new Address($this->getUser()->getEmail(), $this->getUser()->getUserIdentifier()))
                ->subject("Reported Comment")
                ->context([
                    "entity" => $post,
                    "entity_name" => "comment"
                ]);

            $templatedEmail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

            try {
                $mailer->send($templatedEmail);
            } catch (TransportExceptionInterface $e) {
                return new JsonResponse(["message" => $e->getMessage()], 500);
            }
        }

        return new JsonResponse([
            'message' => 'The post was successfully deleted!',
            "userId" => $post->getOwner()->getId()
        ]);
    }

    #[Route('/{id}/react', name: 'react')]
    public function react(
        int $id,
        Request $request
    ): JsonResponse
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
        }

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $reaction = $data['reaction'];

        if ($reaction === 'like' || $reaction == 'dislike') {
            $postReaction = $this->entityManager->getRepository(PostReaction::class)
                ->findOneBy([
                    'post' => $post,
                    'owner' => $this->getUser()
                ]);

            if ($postReaction) {
                if ($reaction === $postReaction->getType()) {
                    $this->entityManager->remove($postReaction);
                } else {
                    $postReaction->setType($reaction);
                }
            } else {
                $postReaction = new PostReaction();

                $postReaction
                    ->setPost($post)
                    ->setOwner($this->getUser())
                    ->setType($reaction);

                $post->addPostReaction($postReaction);

                $this->entityManager->persist($postReaction);
            }

            $this->entityManager->flush();

            $response = [
                'message' => 'Reaction Updated!',
            ];
        } else {
            $response = [
                'message' => 'Incorrect reaction',
            ];
        }

        return new JsonResponse($response);
    }

    #[Route(path: "/{id}/report", name: "report", methods: ["POST"])]
    public function report(?Post $post, MailerInterface $mailer): JsonResponse
    {
        if (!$post) {
            return new JsonResponse(["message" => "The requested post is not found"], 404);
        }

        $adminsMailAddresses = [];
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            if (in_array("ROLE_ADMIN", $user->getRoles())) {
                $adminsMailAddresses[] = $user->getEmail();
            }
        }

        $templatedEmail = (new TemplatedEmail())
            ->htmlTemplate("mail/report_email.html.twig")
            ->to(...$adminsMailAddresses)
            ->from(new Address("moderators@nexus.tech", "Nexus Moderators"))
            ->subject("Reported Comment")
            ->context([
                "entity" => $post,
                "user" => $this->getUser(),
                "entity_name" => "post"
            ]);

        $templatedEmail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        try {
            $mailer->send($templatedEmail);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(["message" => $e->getMessage()], 500);
        }

        return new JsonResponse(["message" => "The comment has been successfully reported. An admin will review the report and take necessary measures"]);
    }

    /**
     * @param Comment $comment
     * @return Comment[]
     */
    public function getAllReplies(Comment $comment): array
    {
        $replies = [];
        foreach ($comment->getReplies() as $reply) {
            $replies[] = $reply;

            if ($reply->getReplies()->count() !== 0) {
                array_push($replies, ...$this->getAllReplies($reply));
            }
        }

        return $replies;
    }

    #[Route('/upload/images', methods: ['POST'])]
    public function uploadImages(
        Request $request,
    ): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'images');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Image upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/images/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/videos', methods: ['POST'])]
    public function uploadVideos(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'videos');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Video upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/videos/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/files', methods: ['POST'])]
    public function uploadFiles(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'post', 'files');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'File upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/posts/files/'. $fileUploadResult
        ]);
    }

    #[Route('/{id}/history', name: 'update_history')]
    public function editHistory(?Post $post): Response
    {
        if (!$post) {
            $this->addFlash('danger', 'The Requested Post is not found');
            return $this->redirectToRoute('app_home');
        }

        if (empty($post->getPostModifications()->toArray()))
            return $this->redirectToRoute('app_home');

        $postModifications = $post->getPostModifications();

        return $this->render('post/update_history.html.twig', [
            'post' => $post,
            'postModifications' => $postModifications,
            'user' => $post->getOwner()
        ]);
    }
}
