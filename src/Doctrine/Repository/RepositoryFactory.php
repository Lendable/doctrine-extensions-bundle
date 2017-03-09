<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * Container to retrieve repositories from.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Map of fully qualified entity class names => service IDs.
     *
     * @var string[]
     */
    private $fqcnToServiceIdMap;

    /**
     * The repository factory to fallback to if the entity is not configured
     * as a service.
     *
     * @var RepositoryFactoryInterface
     */
    private $fallback;

    public function __construct(ContainerInterface $container, array $fqcnToServiceIdMap, RepositoryFactoryInterface $fallback)
    {
        $this->container = $container;
        $this->fqcnToServiceIdMap = $fqcnToServiceIdMap;
        $this->fallback = $fallback;
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $className = $entityManager->getClassMetadata($entityName)->getName();

        if (isset($this->fqcnToServiceIdMap[$className])) {
            return $this->container->get($this->fqcnToServiceIdMap[$className]);
        } else {
            return $this->fallback->getRepository($entityManager, $entityName);
        }
    }
}
