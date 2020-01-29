<?php

namespace Esites\EncryptionBundle\DependencyInjection;

use Esites\EncryptionBundle\Constants\ConfigConstants;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('esites_encryption');

        $rootNode
            ->children()
            ->scalarNode(ConfigConstants::CONFIG_USERNAME_FIELD)
                ->defaultValue('username')
            ->end()
            ->scalarNode(ConfigConstants::CONFIG_USER_CLASS)
            ->end()
            ->scalarNode(ConfigConstants::CONFIG_ENCRYPTION_KEY_FILE)
                ->defaultValue('%kernel.root_dir%/encryption/key')
            ->end()
            ->scalarNode(ConfigConstants::CONFIG_HASH_ALGORITHM)
                ->defaultValue('sha256')
            ->end()
        ;

        return $treeBuilder;
    }
}
