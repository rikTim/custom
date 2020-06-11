<?php

namespace Base\HotelsDbBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('hotels_db');

        $rootNode->children()
            ->scalarNode('default_locale')->defaultValue('en')->end()
            ->arrayNode('translate_fallback')
                ->beforeNormalization()->ifString()->then(function ($v) { return array($v); })->end()
                ->prototype('scalar')->end()
                ->defaultValue(['en'])
            ->end()
            ->scalarNode('entity_manager')->end()
        ->end();

        return $treeBuilder;
    }
}
