<?php

namespace PE\Bundle\OAuth2ServerBundle\Doctrine;

use PE\Bundle\OAuth2ServerBundle\Model\ClientInterface;
use PE\Bundle\OAuth2ServerBundle\Repository\ClientRepositoryInterface;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    // LIBRARY IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function getClientEntity($identifier, $grantType = null, $secret = null, $mustValidateSecret = true)
    {
        $client = $this->findClientByID($identifier);

        if (!$client) {
            return null;
        }

        if ($mustValidateSecret && $client->getSecret() !== $secret) {
            return null;
        }

        return $client;
    }

    // BUNDLE IMPLEMENTATION

    /**
     * @inheritDoc
     */
    public function findClientByID($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @inheritDoc
     */
    public function createClient()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * @inheritDoc
     */
    public function updateClient(ClientInterface $client)
    {
        $manager = $this->getManager();
        $manager->persist($client);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function removeClient(ClientInterface $client)
    {
        $manager = $this->getManager();
        $manager->remove($client);
        $manager->flush();
    }
}