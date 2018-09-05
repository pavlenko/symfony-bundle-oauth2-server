<?php

namespace PE\Bundle\OAuth2ServerBundle\Model;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $redirectUri;

    // LIBRARY IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    // BUNDLE IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier)
    {
        $this->identifier;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @inheritDoc
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }
}