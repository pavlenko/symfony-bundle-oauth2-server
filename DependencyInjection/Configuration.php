<?php

namespace PE\Bundle\OAuth2ServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('pe_oauth2_server');

        $drivers = ['orm', 'mongodb', 'custom'];

        $rootNode
            ->children()
                ->scalarNode('driver')
                    ->validate()
                        ->ifNotInArray($drivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($drivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('object_manager_name')->defaultNull()->end()
                ->arrayNode('class')
                    ->isRequired()
                    ->children()
                        ->scalarNode('access_token')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('auth_code')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('client')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('refresh_token')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('scope')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('repository_access_token')->defaultValue('pe_oauth2_server.repository.access_token.default')->end()
                        ->scalarNode('repository_auth_code')->defaultValue('pe_oauth2_server.repository.auth_code.default')->end()
                        ->scalarNode('repository_client')->defaultValue('pe_oauth2_server.repository.client.default')->end()
                        ->scalarNode('repository_refresh_token')->defaultValue('pe_oauth2_server.repository.refresh_token.default')->end()
                        ->scalarNode('repository_scope')->defaultValue('pe_oauth2_server.repository.scope.default')->end()
                        ->scalarNode('repository_user')->defaultValue('pe_oauth2_server.repository.user.default')->end()
                    ->end()
                ->end()
                ->arrayNode('key')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public')->defaultValue('%kernel.project_dir%/config/oauth-server/public.pem')->end()
                        ->scalarNode('private')->defaultValue('%kernel.project_dir%/config/oauth-server/private.pem')->end()
                        ->scalarNode('passphrase')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('session_key')->defaultValue('pe_oauth_server_request')->end()
                ->scalarNode('login_path')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('grant')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('authorization_code')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                                ->scalarNode('refresh_token_ttl')->defaultValue('P14D')->end()
                            ->end()
                        ->end()
                        ->arrayNode('client_credentials')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                                ->scalarNode('refresh_token_ttl')->defaultValue('P14D')->end()
                            ->end()
                        ->end()
                        ->arrayNode('implicit')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                            ->end()
                        ->end()
                        ->arrayNode('password')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                                ->scalarNode('refresh_token_ttl')->defaultValue('P14D')->end()
                            ->end()
                        ->end()
                        ->arrayNode('refresh_token')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('access_token_ttl')->defaultValue('PT1H')->end()
                                ->scalarNode('refresh_token_ttl')->defaultValue('P14D')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->validate()
                ->ifTrue(function ($v) {
                    return 'custom' === $v['driver']
                        && (
                            'pe_oauth2_server.repository.access_token.default' === $v['service']['repository_access_token'] ||
                            'pe_oauth2_server.repository.auth_code.default' === $v['service']['repository_auth_code'] ||
                            'pe_oauth2_server.repository.client.default' === $v['service']['repository_client'] ||
                            'pe_oauth2_server.repository.refresh_token.default' === $v['service']['repository_refresh_token'] ||
                            'pe_oauth2_server.repository.scope.default' === $v['service']['repository_scope']
                        );
                })
                ->thenInvalid('You need to specify your own services when using the "custom" driver.')
            ->end()
        ;

        return $treeBuilder;
    }
}