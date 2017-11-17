<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Lendable\DoctrineExtensionsBundle\DependencyInjection\CompilerPass\RepositoryServicesCompilerPass;
use Lendable\DoctrineExtensionsBundle\DependencyInjection\LendableDoctrineExtensionsExtension;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\BarBundle;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\Repository\CustomRepositoryWithCustomArgs;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\WithCustomRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\FooBundle\FooBundle;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Service\CustomService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function createTestContainer(): ContainerInterface
    {
        $container = new ContainerBuilder(
            new ParameterBag(
                [
                    'kernel.debug' => true,
                    'kernel.name' => 'tests',
                    'kernel.bundles' => [
                        'FooBundle' => FooBundle::class,
                        'BarBundle' => BarBundle::class,
                    ],
                    'kernel.cache_dir' => sys_get_temp_dir().'/lendable-doctrine-extensions-bundle-test',
                    'kernel.environment' => 'test',
                    'kernel.root_dir' => __DIR__.'/../src',
                ]
            )
        );

        $doctrineExtension = new DoctrineExtension();
        $container->registerExtension($doctrineExtension);

        $doctrineExtension->load(
            [
                [
                    'dbal' => [
                        'connections' => [
                            'default' => [
                                'driver' => 'pdo_sqlite',
                                'charset' => 'UTF8',
                                'memory' => true,
                            ],
                        ],
                    ],
                    'orm' => [
                        'default_entity_manager' => 'default',
                        'entity_managers' => [
                            'default' => [
                                'mappings' => [
                                    'FooBundle' => [
                                        'type' => 'yml',
                                        'dir' => __DIR__.'/Fixtures/Bundles/FooBundle/Resources/config/doctrine',
                                        'prefix' => 'Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\FooBundle\Entity',
                                    ],
                                    'BarBundle' => [
                                        'type' => 'yml',
                                        'dir' => __DIR__.'/Fixtures/Bundles/BarBundle/Resources/config/doctrine',
                                        'prefix' => 'Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $container
        );

        $doctrineExtensionsExtension = new LendableDoctrineExtensionsExtension();
        $container->registerExtension($doctrineExtensionsExtension);

        $doctrineExtensionsExtension->load(
            [
                [
                    'repositories' => [
                        CustomRepositoryWithCustomArgs::class => [
                            'entity' => WithCustomRepository::class,
                            'managers' => ['default'],
                            'args' => [
                                'foo',
                                '%custom_parameter%',
                                '@custom_service',
                                [
                                    'key1' => 'bar',
                                    'key2' => '%custom_parameter%',
                                    'key3' => '@custom_service',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $container
        );

        $container->getCompilerPassConfig()->setBeforeOptimizationPasses([new RepositoryServicesCompilerPass()]);

        $container->setParameter('custom_parameter', 'custom_parameter_value');
        $container->setDefinition('custom_service', new Definition(CustomService::class));

        $container->compile();

        return $container;
    }
}
