# OAuth server symfony bundle
This bundle integrates with [league/oauth2-server](https://oauth2.thephpleague.com/).

## Installation
Install the library via [Composer](https://getcomposer.org/) by
running the following command:
```bash
composer require pe/oauth2-server-bundle
```
Then enable the bundle in your kernel:
```php
<?php
// app/AppKernel.php
class AppKernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new PE\Bundle\OAuth2ServerBundle\PEOAuth2ServerBundle(),
            // ...
        ];
    }
}
```
or for Symfony 4.0
```php
<?php
// SF 4.0 config/bundles.php

return [
    PE\Bundle\OAuth2ServerBundle\PEOAuth2ServerBundle::class => ['all' => true],
];
```

## Configuration
Add to your config
```yaml
pe_oauth2_server:
    driver: orm
    class:
        access_token:  App\Entity\AccessToken
        auth_code:     App\Entity\AuthorizationCode
        client:        App\Entity\Client
        refresh_token: App\Entity\RefreshToken
        scope:         App\Entity\Scope
    login_path: /login/ # This must be login path from configured firewall
    name: Some Auth Server # This required for identifying server
```
Create entities
```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_access_tokens")
 */
class AccessToken extends \PE\Bundle\OAuth2ServerBundle\Model\AccessToken
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @var string
     */
    protected $identifier;
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_authorization_codes")
 */
class AuthorizationCode extends \PE\Bundle\OAuth2ServerBundle\Model\AuthorizationCode
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @var string
     */
    protected $identifier;
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_clients")
 */
class Client extends \PE\Bundle\OAuth2ServerBundle\Model\Client
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @var string
     */
    protected $identifier;
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_refresh_tokens")
 */
class RefreshToken extends \PE\Bundle\OAuth2ServerBundle\Model\RefreshToken
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @var string
     */
    protected $identifier;
}

/**
 * @ORM\Entity()
 * @ORM\Table(name="oauth2_scopes")
 */
class Scope extends \PE\Bundle\OAuth2ServerBundle\Model\Scope
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @var string
     */
    protected $identifier;
}
```
Create event listeners to resolve user identifier, example:
```php
<?php

namespace App\EventSubscriber;

use PE\Bundle\OAuth2ServerBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OAuth2ServerSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvent::GET_USER_BY_CREDENTIALS => 'onGetUserByCredentials',
            UserEvent::GET_USER_BY_OBJECT      => 'onGetUserByObject',
        ];
    }
    
    public function onGetUserByCredentials(UserEvent $event)
    {
        $user = $this->someRepository->findUserByUsername($event->getUsername());
        
        if ($user) {
            $event->setIdentifier($user->getId());
        }
    }
    
    public function onGetUserByObject(UserEvent $event)
    {
        if ($user = $event->getObject()) {
            $event->setIdentifier($user->getId());
        }
    }
}
```