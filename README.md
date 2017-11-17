# Lendable Doctrine Extensions Bundle

[![Build Status](https://travis-ci.org/Lendable/doctrine-extensions-bundle.svg?branch=master)](https://travis-ci.org/Lendable/doctrine-extensions-bundle)
[![Coverage Status](https://coveralls.io/repos/github/Lendable/doctrine-extensions-bundle/badge.svg?branch=travis-ci)](https://coveralls.io/github/Lendable/doctrine-extensions-bundle?branch=travis-ci)
[![Total Downloads](https://poser.pugx.org/lendable/doctrine-extensions-bundle/d/total.png)](https://packagist.org/packages/lendable/doctrine-extensions-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Lendable/doctrine-extensions-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Lendable/doctrine-extensions-bundle/?branch=master)

Licensed under the [MIT License](LICENSE).

Provides extensions to how Doctrine is integrated with Symfony. This includes:

* Allowing repositories to have extra constructor arguments and behind the 
scenes retrieval / instantiation via the container.

## Requirements

* PHP >= 7.0
* symfony/symfony >= 3.2|4.0

## Installation

Require the bundle with Composer:

```bash
composer require lendable/doctrine-extensions-bundle
```

Enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles() 
{
    $bundles = [
      // ...
      new Lendable\DoctrineExtensionsBundle\LendableDoctrineExtensionsBundle(),        
      // ...        
    ];
}
```

## Usage

### Repositories with dependencies

A repository with extra constructor arguments such as:

```php
<?php
// src/App/Entity/Repository/ExampleRepository.php

namespace App\Entity\Repository;

class ExampleRepository extends EntityRepository
{
    public function __construct(
        EntityManager $entityManager, 
        ClassMetadata $classMetadata,
        string $customRawValue,
        string $customParameter, 
        CustomService $customService,
        array $customArray) 
    {
        parent::__construct($entityManager, $classMetadata);
        
        $this->customRawValue = $customRawValue;
        $this->customParameter = $customParameter;
        $this->customService = $customService;
        $this->customArray = $customArray;
    }
}
```

Should be configured to inform the bundle how these extra dependencies should be sourced.

```yaml

lendable_doctrine_extensions:
    repositories:
        App\Entity\Repository\ExampleRepository:
            entity: App\Entity\Example
            managers: [default]
            args:
                - 'a literal raw value'
                - '%custom_parameter%'
                - '@custom_service'
                - 
                    config: '@config_service'
                    raw_value: 'a literal raw value'
```

An argument can either be:

* Raw scalar.
* Parameter reference (`%wrapped%`).
* Service reference (`@prefixed`).
* An indexed/associative array of any of the above.

The repository can now be retrieved as usual via the Doctrine `Registry` or `EntityManager`.

```php
<?php

// Via the registry...

$repository = $container->get('doctrine')->getRepository(App\Entity\Example::class);

// Via the entity manager...

$repository = $container->get('doctrine')->getManager()->getRepository(App\Entity\Example::class);
```
