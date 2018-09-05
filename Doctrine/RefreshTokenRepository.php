<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getNewRefreshToken()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * @inheritDoc
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshToken)
    {
        $manager = $this->getManager();
        $manager->persist($refreshToken);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function revokeRefreshToken($tokenId)
    {
        /* @var $refreshToken RefreshTokenEntityInterface */
        $refreshToken = $this->getRepository()->find($tokenId);

        $manager = $this->getManager();
        $manager->persist($refreshToken);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return empty($this->getRepository()->find($tokenId));
    }
}