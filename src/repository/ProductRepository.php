<?php

namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Product;
use MyAwesomeWebsite\service\OrmHelper;

class ProductRepository extends EntityRepository
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(Product::class);
        parent::__construct($this->entityManager, $entityMetadata);
    }

    public const ITEM_PER_PAGE = 12;

    public function getTotalProductsNumber()
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult(); // execute the query and return the single value ($count)
    }

    public function getProductsPagination($page = 0)
    {
        $dql = "SELECT p FROM MyAwesomeWebsite\model\Product p";
        return $this->entityManager->createQuery($dql)
                                     ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
                                     ->setFirstResult(self::ITEM_PER_PAGE * $page)
                                     ->setMaxResults(self::ITEM_PER_PAGE)->getResult();
    }

    public function findWithCategoriesPaginated(array $categoryNames, int $page)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('c.name IN (:categoryNames)')
            ->setParameter('categoryNames', $categoryNames)
            ->orderBy('p.id', 'DESC');
        $query = $queryBuilder->getQuery();
        $query->setFirstResult(self::ITEM_PER_PAGE * $page)
              ->setMaxResults(self::ITEM_PER_PAGE);

        return new Paginator($query, true);
    }
}

?>
