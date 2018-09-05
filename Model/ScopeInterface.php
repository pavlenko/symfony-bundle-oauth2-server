<?php

namespace PE\Bundle\OAuth2ServerBundle\Model;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

interface ScopeInterface extends ScopeEntityInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @return self
     */
    public function setLabel($label);
}