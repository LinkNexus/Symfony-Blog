<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CommentReaction;
use App\Entity\HiddenComment;
use App\Entity\Post;
use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Collection;
use ReflectionClass;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/comment', name: 'app_comment_')]
#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    public function __construct(
        private readonly FileUploader $fileUploader,
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    #[Route('/{id}/replies', name: 'replies', methods: ['GET', "POST"])]
    public function replies(?Comment $comment): Response
    {
        if (!$comment) {
            $this->addFlash("danger", "The requested Comment is not found");
            return $this->redirectToRoute("app_home");
        }

        foreach ($comment->getOwner()->getBlockedUsers() as $block) {
            if ($block->getBlockedUser() === $this->getUser()) {
                return $this->redirectToRoute("app_home");
            }
        }

        $allReplies = [];
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
        $allReplies = new ArrayCollection($allReplies);

        foreach ($allReplies as $replies) {
            foreach ($replies as $reply) {
                foreach ($reply["comment"]->getOwner()->getBlockedUsers() as $block) {
                    if ($block->getBlockedUser() === $this->getUser()) {
                        $replies->removeElement($reply);
                    }
                }
            }
        }

        return $this->render("comment/replies.html.twig", [
            "user" => $this->getUser(),
            "allReplies" => $allReplies,
            "comment" => $comment,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(
        Request $request
    ): JsonResponse
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $content = $data['content'];
        $post = null;
        $comment = new Comment();

        if (array_key_exists("commentReplied", $data) && $data["commentReplied"]) {
            $respondedComment = $this->entityManager->getRepository(Comment::class)->findOneBy(["id" => $data["commentReplied"]]);

            if ($respondedComment) {
                $post = $respondedComment->getPost();
                $comment->setRespondedComment($respondedComment);
            }
        } else {
            $post = $this->entityManager->getRepository(Post::class)->find($data['post_id']);
        }
        $comment
            ->setPost($post)
            ->setOwner($this->getUser())
            ->setContent($content)
            ->setPost($post);

        if ($this->getUser()->getBlockedTill() >= new \DateTimeImmutable()) {
            return new JsonResponse([
                "message" => "Your profile has been blocked. You can neither post nor comment for " . $this->getUser()->getBlockedTill()->diff(new \DateTimeImmutable())->days . " days"
            ]);
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Comment created!',
            "id" => $comment->getId(),
            "respondedComment" => $comment->getRespondedComment()?->getId(),
            "postId" => $comment->getPost()->getId()
        ]);
    }

    #[Route('/fetch', name: 'fetch')]
    public function fetchComments(Request $request): JsonResponse
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $allComments = [];

        foreach ($data["post_ids"] as $postId) {
            $post = $this->entityManager->getRepository(Post::class)->find($postId);
            $comments = $this->entityManager->getRepository(Comment::class)->findBy(['post' => $post], ["createdAt" => "DESC"]);
            $allComments[] = $comments;
        }

        /* $comments = match ($condition) {
            "newest" => $this->entityManager->getRepository(Comment::class)
                ->findBy(
                    ['post' => $post],
                    ['createdAt' => 'DESC']
                ),
            default => $this->entityManager->getRepository(Comment::class)->findBy(['post' => $post]),
        }; */

        return new JsonResponse($allComments);
    }

    #[Route('/{id}/react', name: 'react')]
    public function react(
        int $id,
        Request $request
    ): JsonResponse
    {
        $comment = $this->entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            $this->addFlash('danger', 'The Requested Comment is not found');
        }

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);
        $reaction = $data['reaction'];

        if ($reaction === 'like' || $reaction == 'dislike') {
            $commentReaction = $this->entityManager->getRepository(CommentReaction::class)
                ->findOneBy([
                    'comment' => $comment,
                    'owner' => $this->getUser()
                ]);

            if ($commentReaction) {
                if ($reaction === $commentReaction->getType()) {
                    $this->entityManager->remove($commentReaction);
                } else {
                    $commentReaction->setType($reaction);
                }
            } else {
                $commentReaction = new CommentReaction();

                $commentReaction
                    ->setComment($comment)
                    ->setOwner($this->getUser())
                    ->setType($reaction);

                $comment->addCommentReaction($commentReaction);

                $this->entityManager->persist($commentReaction);
            }

            $this->entityManager->flush();

            $response = [
                'message' => 'Reaction Updated!',
                'comment' => $comment->getId(),
                'id' => $id
            ];
        } else {
            $response = [
                'message' => 'Incorrect reaction',
                'data' => $data,
            ];
        }

        return new JsonResponse($response);
    }

    #[Route("/{id}/edit", name: 'edit', methods: ['POST'])]
    public function edit(?Comment $comment, Request $request): Response
    {
        if (!$comment) {
            return new JsonResponse(["message" => "The Requested Comment is not found"]);
        }

        if ($this->getUser() !== $comment->getOwner()) {
            return new JsonResponse(["message" => "You can not edit this comment"]);
        }

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);

        $comment->setContent($data['content']);
        $comment->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Comment updated!', "id" => $comment->getId()]);
    }

    #[Route("/{id}/delete", name: 'delete', methods: ['POST'])]
    public function delete(?Comment $comment, MailerInterface $mailer): JsonResponse
    {
        if (!$comment) {
            return new JsonResponse(["message" => "The Requested Comment is not found"]);
        }

        if ($this->getUser() !== $comment->getOwner() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            return new JsonResponse(["message" => "You can not delete this comment"]);
        }

        $commentReactions = $comment->getCommentReactions()->toArray();
        $commentReplies = $this->getAllReplies($comment);
        $hiddenComments = $comment->getHiddenComments()->toArray();

        foreach ($commentReplies as $reply) {
            array_push($commentReactions, ...$reply->getCommentReactions());
            array_push($hiddenComments, ...$reply->getHiddenComments());
            $this->entityManager->remove($reply);
        }

        if (!empty($commentReactions)) {
            foreach ($commentReactions as $reaction) {
                $this->entityManager->remove($reaction);
            }
        }

        foreach ($hiddenComments as $hiddenComment) {
            $this->entityManager->remove($hiddenComment);
        }

        $this->entityManager->remove($comment);
        $this->entityManager->flush();

        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $templatedEmail = (new TemplatedEmail())
                ->htmlTemplate("mail/delete_feedback.html.twig")
                ->to($comment->getOwner()->getEmail())
                ->from(new Address($this->getUser()->getEmail(), $this->getUser()->getUserIdentifier()))
                ->subject("Reported Comment")
                ->context([
                    "entity" => $comment,
                    "entity_name" => "comment"
                ]);

            $templatedEmail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

            try {
                $mailer->send($templatedEmail);
            } catch (TransportExceptionInterface $e) {
                return new JsonResponse(["message" => $e->getMessage()], 500);
            }
        }

        return new JsonResponse(['message' => 'Comment deleted!', "userId" => $comment->getOwner()->getId()]);
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

    #[Route("/{id}/hide", name: 'hide', methods: ['POST'])]
    public function hide(?Comment $comment): JsonResponse
    {
        if (!$comment) {
            return new JsonResponse(["message" => "The Requested Comment is not found"]);
        }

        if ($comment->getOwner() === $this->getUser()) {
            return new JsonResponse(["message" => "You can not hide your own Comment"]);
        }

        $hiddenComment = new HiddenComment();
        $hiddenComment->setComment($comment);
        $hiddenComment->setUser($this->getUser());

        $this->entityManager->persist($hiddenComment);
        $this->entityManager->flush();
        return new JsonResponse(["message" => "The Comment was successfully hidden"]);
    }

    #[Route("/{id}/display", name: 'display', methods: ['POST'])]
    public function display(?Comment $comment): JsonResponse
    {
        if (!$comment) {
            return new JsonResponse(["message" => "The Requested Comment is not found"], 404);
        }

        $hiddenComment = $this->entityManager->getRepository(HiddenComment::class)
            ->findOneBy([
                "comment" => $comment,
                "user" => $this->getUser()
            ]);

        if ($hiddenComment) {
            $this->entityManager->remove($hiddenComment);
            $this->entityManager->flush();

            return new JsonResponse(["content" => $comment->getContent()]);
        }

        return new JsonResponse(null, 404);
    }

    #[Route("/{id}/report", name: 'report', methods: ['POST'])]
    public function report(?Comment $comment, MailerInterface $mailer): JsonResponse
    {
        if (!$comment) {
            return new JsonResponse(["message" => "The requested comment is not found"], 404);
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
                "entity" => $comment,
                "user" => $this->getUser(),
                "entity_name" => "comment"
            ]);

        $templatedEmail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        try {
            $mailer->send($templatedEmail);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(["message" => $e->getMessage()], 500);
        }

        return new JsonResponse(["message" => "The comment has been successfully reported. An admin will review the report and take necessary measures"]);
    }

    /* #[Route("/{id}/getAllReplies", methods: ['GET', "POST"])]
    public function displayAllReplies(Comment $comment)
    {
        dd($this->getAllReplies($comment));
    } */

    #[Route('/upload/images', methods: ['POST'])]
    public function uploadImages(
        Request $request,
    ): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'images');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Image upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/images/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/videos', methods: ['POST'])]
    public function uploadVideos(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'videos');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'Video upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/videos/'. $fileUploadResult
        ]);
    }

    #[Route('/upload/files', methods: ['POST'])]
    public function uploadFiles(Request $request): Response
    {
        $fileUploadResult = $this->fileUploader->moveUploadedFile($request->files->get('file'), 'comment', 'files');

        if ($fileUploadResult instanceof FileException) {
            return new JsonResponse(
                ['error' => 'File upload error'],
                500
            );
        }

        return new JsonResponse([
            'link' => '/uploads/comments/files/'. $fileUploadResult
        ]);
    }
}
