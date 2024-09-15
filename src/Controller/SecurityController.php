<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class SecurityController extends AbstractController
{
    public function __construct(private readonly Security $security)
    {}

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/login/check', name: 'app_login_check')]
    public function check(Request $request): Response
    {
        // get the login link query parameters
        $expires = $request->query->get('expires');
        $username = $request->query->get('user');
        $hash = $request->query->get('hash');

        if (!$expires || !$username || !$hash) {
            return $this->redirectToRoute('app_login');
        }

        // and render a template with the button
        return $this->render('security/process_login_link.html.twig', [
            'expires' => $expires,
            'user' => $username,
            'hash' => $hash
        ]);
    }

    #[Route('/login/request', name: 'app_request_login_link')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->security->getUser();

        if ($user instanceof User && $user->isVerified()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // load the user in some way (e.g., using the form input)
            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $user->setLastLinkRequestedAt(new \DateTimeImmutable());
                $entityManager->flush();

                //create a login link for $user, this returns an instance of
                // LoginLinkDetails
                $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
                $loginLink = $loginLinkDetails->getUrl();

                // create a notification based on the login link details
                $notification = new LoginLinkNotification(
                    $loginLinkDetails,
                    'Welcome to Nexus'
                );

                // create a recipient for this user
                $recipient = new Recipient($user->getEmail());

                // send the notification to the user
                $notifier->send($notification, $recipient);
            }

            $this->addFlash('success', 'A Mail containing the instructions for your Login has been successfully sent to the provided Email Address if an existing Account was linked to it');
            return $this->redirectToRoute('app_request_login_link');
        }

        return $this->render('security/request_login_link.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
