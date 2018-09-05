<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use PE\Bundle\OAuth2ServerBundle\Model\ScopeInterface;
use PE\Bundle\OAuth2ServerBundle\Repository\ScopeRepositoryInterface;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    // LIBRARY IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        return $this->findScopeByName($identifier);
    }

    /**
     * @inheritDoc
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $client,
        $userIdentifier = null
    ) {
        //TODO
        return $scopes;
    }

    // BUNDLE IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function findScopes()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @inheritDoc
     */
    public function findScopeByName($name)
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }

    /**
     * @inheritDoc
     */
    public function countScopes()
    {
        return count($this->getRepository()->findAll());
    }

    /**
     * @inheritDoc
     */
    public function createScope()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * @inheritDoc
     */
    public function updateScope(ScopeInterface $scope)
    {
        $manager = $this->getManager();
        $manager->persist($scope);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function removeScope(ScopeInterface $scope)
    {
        $manager = $this->getManager();
        $manager->remove($scope);
        $manager->flush();
    }
}