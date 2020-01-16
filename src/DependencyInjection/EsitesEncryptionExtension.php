<?php

namespace Esites\EncryptionBundle\DependencyInjection;

use Esites\EncryptionBundle\Constants\ConfigConstants;
use Exception;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EsitesEncryptionExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter(
            ConfigConstants::getParameterKeyName(
                ConfigConstants::CONFIG_USERNAME_FIELD
            ),
            $config[ConfigConstants::CONFIG_USERNAME_FIELD]
        );

        $container->setParameter(
            ConfigConstants::getParameterKeyName(
                ConfigConstants::CONFIG_USER_CLASS
            ),
            $config[ConfigConstants::CONFIG_USER_CLASS]
        );

        $container->setParameter(
            ConfigConstants::getParameterKeyName(
                ConfigConstants::CONFIG_ENCRYPTION_KEY_FILE
            ),
            $config[ConfigConstants::CONFIG_ENCRYPTION_KEY_FILE]
        );

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
