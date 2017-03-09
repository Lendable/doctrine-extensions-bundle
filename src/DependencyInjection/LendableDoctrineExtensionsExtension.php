<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\DependencyInjection;

use Lendable\DoctrineExtensionsBundle\Util\ConfigUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class LendableDoctrineExtensionsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        ConfigUtil::store($container, $config);
    }
}
