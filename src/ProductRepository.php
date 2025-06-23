<?php

namespace MyAwesomeWebsite;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Product;

class ProductRepository extends EntityRepository
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(Product::class);
        parent::__construct($this->entityManager, $entityMetadata);
    }
}

?>
