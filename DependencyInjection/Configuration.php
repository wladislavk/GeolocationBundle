<?php
namespace VKR\GeolocationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vkr_geolocation');
        /** @noinspection PhpUndefinedMethodInspection */
        $rootNode
            ->children()
                ->scalarNode('entity_manager_service')
                    ->defaultValue('doctrine.orm.entity_manager')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
