<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $class = $this->getClass();

        /* @var $accessToken AccessTokenEntityInterface */
        $accessToken = new $class;
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    /**
     * @inheritDoc
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $manager = $this->getManager();
        $manager->persist($accessToken);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function revokeAccessToken($tokenId)
    {
        /* @var $accessToken AccessTokenEntityInterface */
        $accessToken = $this->getRepository()->find($tokenId);

        $manager = $this->getManager();
        $manager->remove($accessToken);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return empty($this->getRepository()->find($tokenId));
    }
}