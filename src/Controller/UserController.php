<?php

namespace App\Controller;

use App\Entity\Block;
use App\Entity\Snooze;
use App\Entity\User;
use App\Entity\Warning;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

#[Route('/user', name: 'app_user_')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{

    use TargetPathTrait;
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/{id}/warn', name: 'warn', methods: ['POST'])]
    public function warn(?User $user, Request $request): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(["message" => "Such a user does not exists here"], 404);
        }

        $data = json_decode($request->getContent(), true);

        $warning = (new Warning())
            ->setUser($user)
            ->setReason($data["reason"]);


        $this->restrict($user);
        $this->entityManager->persist($warning);
        $this->entityManager->flush();

        return new JsonResponse(["message" => "The user has been given a new Warning"]);
    }

    public function restrict(?User $user)//: RedirectResponse
    {
        if ($user->getWarnings()->count() === 5) {
           $user->setBlockedTill((new \DateTimeImmutable())->add(\DateInterval::createFromDateString("5 day")));
        }
    }

    #[Route(path: "/{id}/block", name: "block", methods: ["POST"])]
    public function block(?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(["message" => "Such a user does not exists here"], 404);
        }

        $block = (new Block())
            ->setBlockedUser($user)
            ->setBlockingUser($this->getUser())
        ;

        $this->entityManager->persist($block);
        $this->entityManager->flush();
        return new JsonResponse(["message" => "The user was successfully blocked!"]);
    }

    #[Route(path: "/{id}/unblock", name: "unblock", methods: ["POST"])]
    public function unblock(?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(["message" => "Such a user does not exists here"], 404);
        }

        $block = $this->entityManager->getRepository(Block::class)->findOneBy(["blockedUser" => $user, "blockingUser" => $this->getUser()]);

        if (!$block) {
            return new JsonResponse(["message" => "You did not block this user"], 404);
        }

        $this->entityManager->remove($block);
        $this->entityManager->flush();
        return new JsonResponse(["message" => "The user was successfully unblocked!"]);
    }

    #[Route(path: "/{id}/snooze", name: "snooze", methods: ["POST"])]
    public function snooze(?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(["message" => "Such a user does not exists here"], 404);
        }

        $snooze = (new Snooze())->setSnoozedUser($user)->setSnoozingUser($this->getUser());

        $this->entityManager->persist($snooze);
        $this->entityManager->flush();
        return new JsonResponse(["message" => "The user was successfully snoozed!"]);
    }
}
