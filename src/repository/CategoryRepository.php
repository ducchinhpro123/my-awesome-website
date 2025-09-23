<?php

namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use MyAwesomeWebsite\model\Category;
use MyAwesomeWebsite\service\OrmHelper;

class CategoryRepository extends EntityRepository
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(Category::class);
        parent::__construct($this->entityManager, $entityMetadata);
    }

    public function getCategories()
    {
        return $this->findAll();
    }

    public function getCategoriesFeatured()
    {
        return $this->findBy(["isFeatured" => true]);
    }

    public function findByNames(array $names)
    {
        return $this->findBy(["name" => $names]);
    }

}
