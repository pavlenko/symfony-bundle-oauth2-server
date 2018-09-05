<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

abstract class AbstractRepository
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * @var string
     */
    private $class;

    /**
     * @param ObjectManager $objectManager
     * @param string        $class
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->class         = $class;
    }

    /**
     * Get object manager for specified class
     *
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->objectManager;
    }

    /**
     * Get object repository for specified class
     *
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        if ($this->objectRepository === null) {
            $this->objectRepository = $this->objectManager->getRepository($this->getClass());
        }

        return $this->objectRepository;
    }

    /**
     * Get fully qualified class name
     *
     * @return string
     */
    protected function getClass()
    {
        if (false !== strpos($this->class, ':')) {
            $this->class = $this->objectManager->getClassMetadata($this->class)->getName();
        }

        return $this->class;
    }
}