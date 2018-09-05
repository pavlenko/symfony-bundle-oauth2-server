<?php

namespace PE\Bundle\OAuth2ServerBundle\Model;

use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Scope implements ScopeInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    // LIBRARY IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return ['name' => $this->name, 'label' => $this->label];
    }

    // BUNDLE IMPLEMENTATION

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
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritDoc
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
}