<?php

namespace Paraunit\Tests;

use Paraunit\Lifecycle\CompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class TestContainer
 * @package Paraunit\Tests
 */
class TestContainer
{
    /**
     * @return ContainerBuilder
     */
    public static function getContainer()
    {
        $container = new ContainerBuilder();

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/'));
        $loader->load('services.yml');

        $container->addCompilerPass(
            new CompilerPass(
                'event_dispatcher',
                'paraunit.event_listener',
                'paraunit.event_subscriber'
            )
        );

        $container->compile();

        return $container;
    }
}
