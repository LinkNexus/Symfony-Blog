<?php

namespace App\Security\EventHandler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private readonly UrlGeneratorInterface $router, private readonly Security $security, private readonly EntityManagerInterface $entityManager)
    {}

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $this->security->getUser();
        $session = new Session();

        if ($user instanceof User && $user->isVerified()) {
            $user->setLastLoggedInAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $session->getFlashBag()->add('success', 'You are logged in to Nexus');
            return new RedirectResponse($this->router->generate('app_home'));
        } else {
            $session->getFlashBag()->add('info', 'You need a verified account to access this Website. Check your Mailbox if during after registration, a verification mail from us has been sent to you');
            return $this->security->logout(false);
        }
    }
}