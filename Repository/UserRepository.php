<?php

namespace PE\Bundle\OAuth2ServerBundle\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use PE\Bundle\OAuth2ServerBundle\Event\UserEvent;
use PE\Bundle\OAuth2ServerBundle\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client)
    {
        $this->dispatcher->dispatch(UserEvent::GET_USER_BY_CREDENTIALS, $event = new UserEvent($username, $password));

        return $event->getIdentifier()
            ? new User($event->getIdentifier())
            : null;
    }
}