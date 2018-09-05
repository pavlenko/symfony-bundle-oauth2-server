<?php

namespace PE\Bundle\OAuth2ServerBundle\Repository;

use PE\Bundle\OAuth2ServerBundle\Model\ScopeInterface;

interface ScopeRepositoryInterface extends \League\OAuth2\Server\Repositories\ScopeRepositoryInterface
{
    /**
     * @return ScopeInterface[]
     */
    public function findScopes();

    /**
     * @param string $name
     *
     * @return ScopeInterface|null
     */
    public function findScopeByName($name);

    /**
     * @return int
     */
    public function countScopes();

    /**
     * @return ScopeInterface
     */
    public function createScope();

    /**
     * @param ScopeInterface $scope
     */
    public function updateScope(ScopeInterface $scope);

    /**
     * @param ScopeInterface $scope
     */
    public function removeScope(ScopeInterface $scope);
}