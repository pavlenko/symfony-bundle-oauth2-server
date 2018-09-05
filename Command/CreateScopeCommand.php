<?php

namespace PE\Bundle\OAuth2ServerBundle\Command;

use PE\Bundle\OAuth2ServerBundle\Repository\ScopeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateScopeCommand extends Command
{
    protected static $defaultName = 'pe-oauth2-server:create-scope';

    /**
     * @var ScopeRepositoryInterface
     */
    private $scopeRepository;

    /**
     * @param ScopeRepositoryInterface $scopeRepository
     */
    public function __construct(ScopeRepositoryInterface $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Create scope');
        $this->addArgument('name', InputArgument::REQUIRED, 'Internal scope name');
        $this->addArgument('label', InputArgument::OPTIONAL, 'Display scope name');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $scope = $this->scopeRepository->createScope();

        $scope->setName($input->getArgument('name'));
        $scope->setLabel($input->getArgument('label') ?: $input->getArgument('name'));

        $this->scopeRepository->updateScope($scope);

        $io->success('Scope created successfully!');
    }
}