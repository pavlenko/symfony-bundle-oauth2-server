<?php

namespace PE\Bundle\OAuth2ServerBundle\Event;

class UserEvent
{
    const GET_USER_BY_CREDENTIALS = 'pe_oauth2_server.user.get_by_credentials';
    const GET_USER_BY_OBJECT      = 'pe_oauth2_server.user.get_by_object';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $username
     * @param string $password
     * @param object $object
     */
    public function __construct($username = null, $password = null, $object = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->object   = $object;
    }


    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return self
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }
}