<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\Repository\CustomRepositoryWithCustomArgs;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\WithCustomRepository;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\FooBundle\Entity\WithoutCustomRepository;
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

        $repository = $doctrineRegistry->getRepository(WithoutCustomRepository::class);

        self::assertInstanceOf(EntityRepository::class, $repository);
    }
}
