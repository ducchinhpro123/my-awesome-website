<?php

require_once __DIR__ . '/bootstrap.php';

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;

return DependencyFactory::fromEntityManager(
    new Configuration(),
    new ExistingEntityManager($entityManager)
);
