# Lendable Doctrine Extensions Bundle

[![Build Status](https://secure.travis-ci.org/Lendable/doctrine-extensions-bundle.png)](http://travis-ci.org/Lendable/doctrine-extensions-bundle)
[![Coverage Status](https://coveralls.io/repos/github/Lendable/doctrine-extensions-bundle/badge.svg?branch=travis-ci)](https://coveralls.io/github/Lendable/doctrine-extensions-bundle?branch=travis-ci)
[![Total Downloads](https://poser.pugx.org/lendable/doctrine-extensions-bundle/d/total.png)](https://packagist.org/packages/lendable/doctrine-extensions-bundle)

Licensed under the [MIT License](LICENSE).

Provides extensions to how Doctrine is integrated with Symfony. This includes:

* Allowing repositories to have extra constructor arguments and behind the 
scenes retrieval / instantiation via the container.

## Requirements

* PHP >= 7.0
* symfony/symfony >= 3.0 

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
        CustomService $customService) 
    {
        parent::__construct($entityManager, $classMetadata);
        
        $this->customRawValue = $customRawValue;
        $this->customParameter = $customParameter;
        $this->customService = $customService;
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
```

An argument can either be:

* Raw value (including an array).
* Parameter reference (`%wrapped%`).
* Service reference (`@prefixed`).

The repository can now be retrieved as usual via the Doctrine `Registry` or `EntityManager`.

```php
<?php

// Via the registry...

$repository = $container->get('doctrine')->getRepository(App\Entity\Example::class);

// Via the entity manager...

$repository = $container->get('doctrine')->getManager()->getRepository(App\Entity\Example::class);
```

## Contributing

Pull requests are welcome. Please see our
[CONTRIBUTING](https://github.com/symfony-cmf/symfony-cmf/blob/master/CONTRIBUTING.md)
guide.

Unit and/or functional tests exist for this bundle. See the
[Testing documentation](http://symfony.com/doc/master/cmf/components/testing.html)
for a guide to running the tests.

Thanks to
[everyone who has contributed](https://github.com/symfony-cmf/MyBundle/contributors) already.
