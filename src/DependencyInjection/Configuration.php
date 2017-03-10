<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('lendable_doctrine_extensions');

        $this->appendRepositories($root);

        return $treeBuilder;
    }

    private function appendRepositories(ArrayNodeDefinition $root): ArrayNodeDefinition
    {
        $repositories = $root->children()
            ->arrayNode('repositories')
            ->prototype('array');

        assert($repositories instanceof ArrayNodeDefinition);

        $repositories->children()
            ->arrayNode('args')
            ->isRequired()
            ->prototype('variable');

        $repositories->children()
            ->arrayNode('managers')
            ->requiresAtLeastOneElement()
            ->prototype('scalar')
            ->defaultValue(['default']);

        $repositories->children()
            ->scalarNode('entity')
            ->isRequired()
            ->validate()
            ->ifTrue(
                function ($v) {
                    return !class_exists($v);
                }
            )
            ->thenInvalid('Entity class does not exist');

        return $root;
    }
}
