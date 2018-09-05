<?php

namespace PE\Bundle\OAuth2ServerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class GenerateKeyPairCommand extends Command
{
    protected static $defaultName = 'pe-oauth2-server:generate-key-pair';

    /**
     * @var string
     */
    private $keyPublic;

    /**
     * @var string
     */
    private $keyPrivate;

    /**
     * @var string
     */
    private $keyPassPhrase;

    /**
     * @param string $keyPublic
     * @param string $keyPrivate
     * @param string $keyPassPhrase
     */
    public function __construct($keyPublic, $keyPrivate, $keyPassPhrase)
    {
        $this->keyPublic     = $keyPublic;
        $this->keyPrivate    = $keyPrivate;
        $this->keyPassPhrase = $keyPassPhrase;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Generate a RSA key pair for use in your OAuth2 server');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Request passphrase');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Generating key pair...');

        $keyPair = openssl_pkey_new([
            'digest_alg'       => 'sha256',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($keyPair, $privateKey, $this->keyPassPhrase ?: null);

        $publicKey = openssl_pkey_get_details($keyPair)['key'];

        $fs = new Filesystem();

        if ($fs->exists([$this->keyPublic, $this->keyPrivate]) && !$input->getOption('force')) {
            throw new IOException('Unable to store keys files because they already exist on disk.');
        }

        $io->text('Storing keys...');

        $fs->dumpFile($this->keyPublic, $publicKey);
        $fs->dumpFile($this->keyPrivate, $privateKey);

        $fs->chmod([$this->keyPublic, $this->keyPrivate], 0660);

        $io->success('Keys generated successfully!');
    }
}