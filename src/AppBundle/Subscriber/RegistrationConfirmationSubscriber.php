<?php


namespace AppBundle\Subscriber;


use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RegistrationConfirmationSubscriber implements EventSubscriberInterface
{
    /** @var Router */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationConfirm'];
    }

    public function onRegistrationConfirm(FilterUserResponseEvent $event)
    {
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $response->setTargetUrl($this->router->generate('teaser_route'));
    }
}
