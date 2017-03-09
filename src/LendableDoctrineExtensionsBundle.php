<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle;

use Lendable\DoctrineExtensionsBundle\DependencyInjection\CompilerPass\RepositoryServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LendableDoctrineExtensionsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RepositoryServicesCompilerPass());
    }

}
