<?php

namespace PE\Bundle\OAuth2ServerBundle\Repository;

use PE\Bundle\OAuth2ServerBundle\Model\ClientInterface;

interface ClientRepositoryInterface extends \League\OAuth2\Server\Repositories\ClientRepositoryInterface
{
    /**
     * @param string $id
     *
     * @return ClientInterface|null
     */
    public function findClientByID($id);

    /**
     * @return ClientInterface
     */
    public function createClient();

    /**
     * @param ClientInterface $client
     */
    public function updateClient(ClientInterface $client);

    /**
     * @param ClientInterface $client
     */
    public function removeClient(ClientInterface $client);
}