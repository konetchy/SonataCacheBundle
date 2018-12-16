<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sonata_cache');

        // Keep compatibility with symfony/config < 4.2
        if (!\method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->root('sonata_cache')->children();
        } else {
            $node = $treeBuilder->getRootNode()->children();
        }

        $node
            ->arrayNode('cache_invalidation')
                ->addDefaultsIfNotSet()
                ->children()
                    ->enumNode('orm_listener')
                        ->values([true, false, 'auto'])
                        ->defaultValue('auto')
                    ->end()
                    ->enumNode('phpcr_odm_listener')
                        ->values([true, false, 'auto'])
                        ->defaultValue('auto')
                    ->end()
                    ->scalarNode('service')->defaultValue('sonata.cache.invalidation.simple')->end()
                    ->scalarNode('recorder')->defaultValue('sonata.cache.recorder')->end()
                    ->arrayNode('classes')
                        ->useAttributeAsKey('id')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()

            ->scalarNode('default_cache')->defaultValue(false)->end()
            ->scalarNode('default_counter')->defaultValue(false)->end()

            ->arrayNode('caches')
                ->children()
                    ->arrayNode('esi')
                        ->children()
                            ->scalarNode('token')->defaultValue(hash('sha256', uniqid((string) mt_rand(), true)))->end()
                            ->scalarNode('version')->defaultValue(2)->end()
                            ->arrayNode('servers')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('ssi')
                        ->children()
                            ->scalarNode('token')->defaultValue(hash('sha256', uniqid((string) mt_rand(), true)))->end()
                        ->end()
                    ->end()

                    ->arrayNode('mongo')
                        ->children()
                            ->scalarNode('database')->isRequired()->end()
                            ->scalarNode('collection')->isRequired()->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(27017)->end()
                                        ->scalarNode('host')->isRequired()->end()
                                        ->scalarNode('user')->defaultValue(null)->end()
                                        ->scalarNode('password')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('memcached')
                        ->children()
                            ->scalarNode('prefix')->isRequired()->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(11211)->end()
                                        ->scalarNode('host')->defaultValue('localhost')->end()
                                        ->integerNode('weight')->defaultValue(0)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('predis')
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(6379)->end()
                                        ->scalarNode('host')->defaultValue('localhost')->end()
                                        ->integerNode('database')->isRequired()->end()
                                        ->scalarNode('password')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('apc')
                        ->children()
                            ->scalarNode('token')->isRequired()->end()
                            ->scalarNode('prefix')->isRequired()->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('domain')->isRequired()->end()
                                        ->scalarNode('ip')->isRequired()->end()
                                        ->integerNode('port')->defaultValue(80)->end()
                                        ->scalarNode('basic')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('timeout')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('RCV')
                                        ->prototype('scalar')->end()
                                    ->end()
                                    ->arrayNode('SND')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('symfony')
                        ->children()
                            ->scalarNode('token')->isRequired()->end()
                            ->variableNode('types')
                                ->defaultValue(['all', 'annotations', 'doctrine', 'sonata', 'translations', 'twig'])
                                ->validate()
                                    ->ifString()
                                    ->thenInvalid('Symfony cache types must be an array [value1, value2, ...]')
                                ->end()
                                ->validate()
                                    ->ifNull()
                                    ->thenInvalid('Symfony cache types cannot be null')
                                ->end()
                            ->end()
                            ->booleanNode('php_cache_enabled')->defaultValue(false)->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('domain')->isRequired()->end()
                                        ->scalarNode('ip')->isRequired()->end()
                                        ->integerNode('port')->defaultValue(80)->end()
                                        ->scalarNode('basic')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('timeout')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('RCV')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->integerNode('sec')
                                                ->info('Timeout value specifying the amount of seconds for input operations')
                                                ->defaultValue(2)
                                            ->end()
                                            ->integerNode('usec')
                                                ->info('Timeout value specifying the amount of microseconds for input operations')
                                                ->defaultValue(0)
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('SND')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->integerNode('sec')
                                                ->info(<<<'INFO'
Timeout value specifying the amount of seconds that an output function
blocks because flow control prevents data from being sent
INFO
                                                )
                                                ->defaultValue(2)
                                            ->end()
                                            ->integerNode('usec')
                                                ->info(<<<'INFO'
Timeout value specifying the amount of microseconds that an output function
blocks because flow control prevents data from being sent
INFO
                                                )
                                                ->defaultValue(0)
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('counters')
                ->children()
                    ->arrayNode('mongo')
                        ->children()
                            ->scalarNode('database')->isRequired()->end()
                            ->scalarNode('collection')->isRequired()->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(27017)->end()
                                        ->scalarNode('host')->isRequired()->end()
                                        ->scalarNode('user')->defaultValue(null)->end()
                                        ->scalarNode('password')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('memcached')
                        ->children()
                            ->scalarNode('prefix')->isRequired()->end()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(11211)->end()
                                        ->scalarNode('host')->defaultValue('localhost')->end()
                                        ->integerNode('weight')->defaultValue(0)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('predis')
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->integerNode('port')->defaultValue(6379)->end()
                                        ->scalarNode('host')->defaultValue('localhost')->end()
                                        ->integerNode('database')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('apc')
                        ->children()
                            ->scalarNode('prefix')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
