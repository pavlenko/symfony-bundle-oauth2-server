<?php

namespace PE\Bundle\OAuth2ServerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class PEOAuth2ServerExtension extends Extension
{
    /**
     * @var array
     */
    private static $drivers = [
        'orm' => [
            'registry' => 'doctrine',
        ],
        'mongodb' => [
            'registry' => 'doctrine_mongodb',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if ('custom' !== $config['driver']) {
            if (!isset(self::$drivers[$config['driver']])) {
                throw new \RuntimeException('Unknown driver');
            }

            // Set registry alias
            $container->setAlias(
                'pe_oauth2_server.doctrine_registry',
                new Alias(self::$drivers[$config['driver']]['registry'], false)
            );

            // Set factory to object manager
            $definition = $container->getDefinition('pe_oauth2_server.object_manager');
            $definition->setFactory([new Reference('pe_oauth2_server.doctrine_registry'), 'getManager']);

            // Set manager name to access in config
            $container->setParameter('pe_oauth2_server.object_manager_name', $config['object_manager_name']);

            // Set parameter for switch mapping
            $container->setParameter('pe_oauth2_server.backend_type.' . $config['driver'], true);

            // Set classes to use in default services
            $container->setParameter('pe_oauth2_server.class.access_token', $config['class']['access_token']);
            $container->setParameter('pe_oauth2_server.class.auth_code', $config['class']['auth_code']);
            $container->setParameter('pe_oauth2_server.class.client', $config['class']['client']);
            $container->setParameter('pe_oauth2_server.class.refresh_token', $config['class']['refresh_token']);
            $container->setParameter('pe_oauth2_server.class.scope', $config['class']['scope']);
        }

        // Set aliases to services
        $container->setAlias('pe_oauth2_server.repository.access_token', new Alias($config['service']['repository_access_token'], true));
        $container->setAlias('pe_oauth2_server.repository.auth_code', new Alias($config['service']['repository_auth_code'], true));
        $container->setAlias('pe_oauth2_server.repository.client', new Alias($config['service']['repository_client'], true));
        $container->setAlias('pe_oauth2_server.repository.refresh_token', new Alias($config['service']['repository_refresh_token'], true));
        $container->setAlias('pe_oauth2_server.repository.scope', new Alias($config['service']['repository_scope'], true));
        $container->setAlias('pe_oauth2_server.repository.user', new Alias($config['service']['repository_user'], true));

        // Set key parameters
        $container->setParameter('pe_oauth2_server.key.public', $config['key']['public']);
        $container->setParameter('pe_oauth2_server.key.private', $config['key']['private']);
        $container->setParameter('pe_oauth2_server.key.passphrase', $config['key']['passphrase']);

        // Set security required parameters
        $container->setParameter('pe_oauth2_server.session_key', $config['session_key']);
        $container->setParameter('pe_oauth2_server.name', $config['name']);
        $container->setParameter('pe_oauth2_server.login_path', $config['login_path']);

        // Set grant parameters
        $container->setParameter('pe_oauth2_server.grant.authorization_code.access_token_ttl', $config['grant']['authorization_code']['access_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.authorization_code.refresh_token_ttl', $config['grant']['authorization_code']['refresh_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.client_credentials.access_token_ttl', $config['grant']['client_credentials']['access_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.client_credentials.refresh_token_ttl', $config['grant']['client_credentials']['refresh_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.implicit.access_token_ttl', $config['grant']['implicit']['access_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.password.access_token_ttl', $config['grant']['password']['access_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.password.refresh_token_ttl', $config['grant']['password']['refresh_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.refresh_token.access_token_ttl', $config['grant']['refresh_token']['access_token_ttl']);
        $container->setParameter('pe_oauth2_server.grant.refresh_token.refresh_token_ttl', $config['grant']['refresh_token']['refresh_token_ttl']);

        // Process grants
        $definition = $container->getDefinition('pe_oauth2_server.authorization_server');
        foreach ($config['grant'] as $name => $options) {
            if ($options['enabled']) {
                $definition->addMethodCall('enableGrantType', [
                    new Reference('pe_oauth2_server.grant.' . $name),
                    new Reference('pe_oauth2_server.grant.' . $name . '.access_token_ttl'),
                ]);
            }
        }

        if (class_exists(\GuzzleHttp\Psr7\ServerRequest::class)) {
            $container->setAlias(
                'pe_oauth2_server.request_converter',
                new Alias('pe_oauth2_server.request_converter.guzzle', true)
            );
        } elseif (class_exists(\Zend\Diactoros\ServerRequest::class)) {
            $container->setAlias(
                'pe_oauth2_server.request_converter',
                new Alias('pe_oauth2_server.request_converter.diactoros', true)
            );
        } else {
            throw new \RuntimeException(sprintf(
                'One of %s composer packages is required',
                json_encode(['guzzlehttp/psr7', 'zendframework/zend-diactoros'])
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'pe_oauth2_server';
    }
}