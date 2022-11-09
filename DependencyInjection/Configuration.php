<?php

namespace Valiton\Payment\DatatransBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('valiton_payment_datatrans')
            ->children()
                ->scalarNode('payment_url')->isRequired()->end()
                ->scalarNode('settlement_url')->isRequired()->end()
                ->scalarNode('merchant_id')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->scalarNode('hmac_key')->isRequired()->end()
                ->scalarNode('return_url')->defaultNull()->end()
                ->scalarNode('error_url')->defaultNull()->end()
                ->scalarNode('cancel_url')->defaultNull()->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
