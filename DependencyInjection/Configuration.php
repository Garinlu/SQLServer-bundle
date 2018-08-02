<?php

namespace garinlu\SQLServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('garinlu_sql_server');

        $rootNode
            ->children()
                ->scalarNode('serverName')
                    ->defaultValue('')
                ->end()
                ->scalarNode('UID')
                    ->defaultValue('')
                ->end()
                ->scalarNode('PWD')
                    ->defaultValue('')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
