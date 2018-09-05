<?php

namespace PE\Bundle\OAuth2ServerBundle\Command;

use Defuse\Crypto\Key;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use PE\Bundle\OAuth2ServerBundle\Repository\ClientRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateClientCommand extends Command
{
    protected static $defaultName = 'pe-oauth2-server:create-client';

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @param ClientRepositoryInterface $clientRepository
     */
    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->addArgument('name', InputArgument::REQUIRED, 'Client name');
        $this->addArgument('redirect_uri', InputArgument::REQUIRED, 'Redirect uri');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Creating client...');

        //TODO check name unique?

        $client = $this->clientRepository->createClient();

        $client->setName($input->getArgument('name'));
        $client->setRedirectUri($input->getArgument('redirect_uri'));
        $client->setSecret(substr(Key::createNewRandomKey()->saveToAsciiSafeString(), 0, 32));

        $this->clientRepository->updateClient($client);

        $io->success('Client created successfully, below your access credentials:');
        $io->listing([
            'client_id:     ' . $client->getIdentifier(),
            'client_secret: ' . $client->getSecret(),
        ]);
    }
}