<?php

declare(strict_types=1);

namespace Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Bundles\BarBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Lendable\DoctrineExtensionsBundle\Tests\Fixtures\Service\CustomService;

class CustomRepositoryWithCustomArgs extends EntityRepository
{
    /**
     * @var string
     */
    private $customScalar;

    /**
     * @var CustomService
     */
    private $customService;

    /**
     * @var string
     */
    private $customParameter;

    /**
     * @var array
     */
    private $customArray;

    public function __construct(EntityManager $em, Mapping\ClassMetadata $class, string $customScalar, string $customParameter, CustomService $customService, array $customArray)
    {
        parent::__construct($em, $class);
        $this->customScalar = $customScalar;
        $this->customParameter = $customParameter;
        $this->customService = $customService;
        $this->customArray = $customArray;
    }

    /**
     * @return string
     */
    public function getCustomParameter(): string
    {
        return $this->customParameter;
    }

    /**
     * @return string
     */
    public function getCustomScalar(): string
    {
        return $this->customScalar;
    }

    /**
     * @return CustomService
     */
    public function getCustomService(): CustomService
    {
        return $this->customService;
    }

    /**
     * @return array
     */
    public function getCustomArray(): array
    {
        return $this->customArray;
    }
}
