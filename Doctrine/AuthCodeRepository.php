<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getNewAuthCode()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * @inheritDoc
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCode)
    {
        $manager = $this->getManager();
        $manager->persist($authCode);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function revokeAuthCode($codeId)
    {
        /* @var $authCode AuthCodeEntityInterface */
        $authCode = $this->getRepository()->find($codeId);

        $manager = $this->getManager();
        $manager->remove($authCode);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function isAuthCodeRevoked($codeId)
    {
        return empty($this->getRepository()->find($codeId));
    }
}