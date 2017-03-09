<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\DependencyInjection\CompilerPass;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Lendable\DoctrineExtensionsBundle\Doctrine\Repository\RepositoryFactory;
use Lendable\DoctrineExtensionsBundle\Util\ConfigUtil;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RepositoryServicesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = ConfigUtil::fetch($container)['repositories'];
        assert(is_array($config));

        /** Map of name => definition of entity managers that have already been processed. */
        $configuredManagers = [];

        /** Map of manager name => definition of repository factories that have been processed. */
        $repositoryFactories = [];

        /** Ensures services are not tagged with the same manager & entity combination. */
        $managerEntityMap = [];

        foreach ($config as $repositoryFqcn => $repoConfig) {
            /** @var string[] $managerNames */
            $managerNames = $repoConfig['managers'];
            $entityFqcn = $repoConfig['entity'];

            foreach ($managerNames as $managerName) {
                if (isset($managerEntityMap[$managerName][$entityFqcn])) {
                    throw new InvalidConfigurationException(sprintf('The entity %s has been defined on multiple repository services for the same entity manager', $entityFqcn));
                }

                $managerEntityMap[$managerName][$entityFqcn] = true;

                if (!isset($configuredManagers[$managerName])) {
                    $repositoryFactories[$managerName] = $this->createAndRegisterRepositoryFactory($container, $managerName);
                    $configuredManagers[$managerName] = true;
                }

                $this->createRepositoryService($container, $managerName, $repositoryFqcn, $entityFqcn, $repositoryFactories[$managerName], $repoConfig);
            }
        }
    }

    private function createManagerConfigurationId(string $managerName): string
    {
        return sprintf('doctrine.orm.%s_configuration', $managerName);
    }

    private function createRepositoryFactoryDefinition(): Definition
    {
        $definition = new Definition(RepositoryFactory::class);
        $definition->setArguments(
            [
                new Reference('service_container'),
                [], // IDs to be populated later.
                new Definition(DefaultRepositoryFactory::class),
            ]
        );

        return $definition;
    }

    private function createAndRegisterRepositoryFactory(ContainerBuilder $container, string $managerName): Definition
    {
        $configuration = $container->getDefinition($this->createManagerConfigurationId($managerName));

        $repositoryFactory = $this->createRepositoryFactoryDefinition();

        $configuration->removeMethodCall('setRepositoryFactory');
        $configuration->addMethodCall('setRepositoryFactory', [$repositoryFactory]);

        return $repositoryFactory;
    }

    private function createRepositoryService(ContainerBuilder $container, string $managerName, string $repositoryFqcn, string $entityFqcn, Definition $repositoryFactoryDefinition, array $config): string
    {
        $repositoryDefinition = new Definition($repositoryFqcn);
        $repositoryDefinition->setPublic(true);

        // Create inline factory definitions.

        $managerDefinition = new Definition(EntityManagerInterface::class);
        $managerDefinition->setFactory([new Reference('doctrine'), 'getManager']);
        $managerDefinition->setArguments([$managerName]);

        $metadataDefinition = new Definition(ClassMetadata::class);
        $metadataDefinition->setFactory([$managerDefinition, 'getClassMetadata']);
        $metadataDefinition->setArguments([$entityFqcn]);

        // First 2 args are always EM & meta.

        $repositoryDefinition->addArgument($managerDefinition);
        $repositoryDefinition->addArgument($metadataDefinition);

        assert(is_array($config['args']));

        // Add custom args.

        foreach ($config['args'] as $arg) {
            $repositoryDefinition->addArgument($this->createArgument($arg));
        }

        $hash = hash('sha256', $entityFqcn);
        $id = sprintf('lendable.doctrine_extensions.repositories.%s.%s', $managerName, $hash);

        $fqcnToServiceIdMap = $repositoryFactoryDefinition->getArgument(1);
        $fqcnToServiceIdMap[$entityFqcn] = $id;
        $repositoryFactoryDefinition->replaceArgument(1, $fqcnToServiceIdMap);

        $container->setDefinition($id, $repositoryDefinition);

        return $id;
    }

    private function createArgument($configValue)
    {
        if (is_string($configValue) && $configValue[0] === '@') {
            return new Reference(mb_substr($configValue, 1));
        } else {
            return $configValue;
        }
    }
}
