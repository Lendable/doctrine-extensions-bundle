<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\Repository\CustomRepositoryWithCustomArgs;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\WithCustomRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\FooBundle\Entity\WithoutCustomRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Service\CustomService;
use Lendable\DoctrineExtensionsBundle\Tests\TestCase;

class RepositoryResolutionTest extends TestCase
{
    public function test_repository_is_registered_as_a_service()
    {
        $container = $this->createTestContainer();

        self::assertTrue($container->has(sprintf('lendable.doctrine_extensions.repositories.default.%s', hash('sha256', WithCustomRepository::class))));
    }

    public function test_repository_with_args_is_retrievable_via_registry()
    {
        $container = $this->createTestContainer();

        $doctrineRegistry = $container->get('doctrine');
        assert($doctrineRegistry instanceof Registry);

        $repository = $doctrineRegistry->getRepository(WithCustomRepository::class);

        self::assertInstanceOf(CustomRepositoryWithCustomArgs::class, $repository);
        /** @var CustomRepositoryWithCustomArgs $repository */

        self::assertSame('foo', $repository->getCustomScalar());
        self::assertSame('custom_parameter_value', $repository->getCustomParameter());
        self::assertInstanceOf(CustomService::class, $repository->getCustomService());
        self::assertCount(3, $repository->getCustomArray());
        self::assertArrayHasKey('key1', $repository->getCustomArray());
        self::assertSame('bar', $repository->getCustomArray()['key1']);
        self::assertArrayHasKey('key2', $repository->getCustomArray());
        self::assertSame('custom_parameter_value', $repository->getCustomArray()['key2']);
        self::assertArrayHasKey('key3', $repository->getCustomArray());
        self::assertInstanceOf(CustomService::class, $repository->getCustomArray()['key3']);

        $repository = $doctrineRegistry->getRepository(WithoutCustomRepository::class);

        self::assertInstanceOf(EntityRepository::class, $repository);
    }
}
