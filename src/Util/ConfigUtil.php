<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Stores and fetches configuration via an abstract service in the container.
 *
 * This service will be cleaned up during the late phase of container optimization.
 */
final class ConfigUtil
{
    const SERVICE_ID = 'lendable_doctrine_extensions_config';

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Fetches bundle configuration from the container.
     *
     * @param ContainerBuilder $containerBuilder
     *
     * @return array
     */
    public static function fetch(ContainerBuilder $containerBuilder): array
    {
        return $containerBuilder->getDefinition(self::SERVICE_ID)->getArgument(0);
    }

    /**
     * Stores bundle configuration in the container temporarily.
     *
     * @param ContainerBuilder $containerBuilder
     * @param array $config
     */
    public static function store(ContainerBuilder $containerBuilder, array $config)
    {
        $definition = new Definition(\stdClass::class, [$config]);
        $definition->setAbstract(true);

        $containerBuilder->setDefinition(self::SERVICE_ID, $definition);
    }
}
