<?php

namespace PE\Bundle\OAuth2ServerBundle\Model;

use League\OAuth2\Server\Entities\ClientEntityInterface;

interface ClientInterface extends ClientEntityInterface
{
    // LIBRARY MISSING METHODS

    /**
     * @param string $identifier
     *
     * @return self
     */
    public function setIdentifier($identifier);

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName($name);

    /**
     * @param string $redirectUri
     *
     * @return self
     */
    public function setRedirectUri($redirectUri);

    // ADDITIONAL METHODS

    /**
     * @return string
     */
    public function getSecret();

    /**
     * @param string $secret
     *
     * @return self
     */
    public function setSecret($secret);
}