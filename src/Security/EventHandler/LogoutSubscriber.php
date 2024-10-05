<?php

namespace App\Security\EventHandler;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(): void
    {
        $session = new Session();

        if ($this->security->getUser()->isVerified()) {
            $session->getFlashBag()->add('info', 'You have been successfully logged out from Nexus');
        } else {
            $session->getFlashBag()->add('info', 'You need a verified account to access this Website. Check your Mailbox if during after registration, a verification mail from us has been sent to you');
        }
    }
}