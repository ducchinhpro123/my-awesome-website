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

    /**
     * How many products in this database?
     *
     * @return int total products
     */
    public function getTotalProductsNumber()
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult(); // execute the query and return the single value ($count)
    }

    /**
     * Get products pagination, default 12 items per page
     *
     * @param int $page (optional) how many pages do you want to get?
     *
     * @return Product[] The list of Product object
     */
    public function getProductsPagination($page = 0)
    {
        $dql = "SELECT p FROM MyAwesomeWebsite\model\Product p";
        return $this->entityManager->createQuery($dql)
                                     ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
                                     ->setFirstResult(self::ITEM_PER_PAGE * $page)
                                     ->setMaxResults(self::ITEM_PER_PAGE)->getResult();
    }
    /**
     * @param array<int,mixed> $categoryNames
     */
    public function findWithCategoriesPaginated(array $categoryNames, int $page)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('c.name IN (:categoryNames)')
            ->setParameter('categoryNames', $categoryNames)
            ->orderBy('p.id', 'DESC');
        $query = $queryBuilder->getQuery();
        return $query->setFirstResult(self::ITEM_PER_PAGE * $page)
              ->setMaxResults(self::ITEM_PER_PAGE)
              ->getResult();

        /* return new Paginator($query, true); */
    }

    /**
     * @param array<int,mixed> $criteria
     */
    public function findWithFilters(array $criteria, int $page): Paginator
    {
        /* Do not call the database for each filter and try to merge the results in PHP.
         * The database is infinitely more efficient at finding the intersection of multiple
         * criteria than PHP is at merging arrays. */
        $queryBuilder = $this->createQueryBuilder('p');

        if (!empty($criteria['categories'])) {
            $queryBuilder->join('p.category', 'c')
                ->andWhere('c.name IN (:categoryNames)')
                ->setParameter('categoryNames', $criteria['categories']);
        }

        if (!empty($criteria['searchTerm'])) {
            $searchTerms = explode(' ', trim($criteria['searchTerm']));
            $conditions = [];

            foreach ($searchTerms as $index => $term) {
                $paramName = "term{$index}";
                $conditions[] = $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('p.name', ':' . $paramName),
                    $queryBuilder->expr()->like('p.description', ':' . $paramName),
                );
                $queryBuilder->setParameter($paramName, '%' . $term . '%');
            }
            if (!empty($conditions)) {
                $queryBuilder->andWhere($queryBuilder->expr()->andX(...$conditions));
            }
        }

        if (!empty($criteria['prices'])) {
            $priceConditions = [];
            $paramIndex = 0;
            foreach ($criteria['prices'] as $range) {
                if (str_ends_with($range, '+')) {
                    $minPrice = (int) rtrim($range, '+');
                    $priceConditions[] = "p.price >= :min{$paramIndex}";
                    $queryBuilder->setParameter("min{$paramIndex}", $minPrice);
                } else {
                    $parts = explode('-', $range);
                    if (count($parts) === 2) {
                        $priceConditions[] = "(p.price >= :min{$paramIndex} AND p.price <= :max{$paramIndex})";
                        $queryBuilder->setParameter("min{$paramIndex}", (int)$parts[0]);
                        $queryBuilder->setParameter("max{$paramIndex}", (int)$parts[1]);
                    }
                }
                $paramIndex++;
            }
            if (!empty($priceConditions)) {
                $queryBuilder->andWhere('(' . implode(' OR ', $priceConditions) . ')');
            }
        }

        /* <option value="featured">Sắp xếp: Nổi bật</option> */
        /* <option value="price_asc">Giá: Thấp đến Cao</option> */
        /* <option value="price_desc">Giá: Cao đến Thấp</option> */
        /* <option value="name_asc">Tên: A đến Z</option> */
        /* <option value="name_desc">Tên: Z đến A</option> */
        /* <option value="rating_asc">Đánh giá: Cao đến Thấp</option> */
        if (!empty($criteria['sort_by'])) {
            switch ($criteria['sort_by']) {
                case 'price_asc':
                    $queryBuilder->orderBy('p.price', 'ASC');
                    break;
                case 'price_desc':
                    $queryBuilder->orderBy('p.price', 'DESC');
                    break;
                case 'name_asc':
                    $queryBuilder->orderBy('p.name', 'ASC');
                    break;
                case 'name_desc':
                    $queryBuilder->orderBy('p.name', 'DESC');
                    break;
                case 'rating_asc':
                    $queryBuilder->orderBy('p.rating', 'ASC');
                    break;
                case 'rating_desc':
                    $queryBuilder->orderBy('p.rating', 'DESC');
                    break;
                case 'featured':
                default:
                    $queryBuilder->orderBy('p.id', 'DESC');
            }
        }

        $query = $queryBuilder->getQuery()
            ->setFirstResult(self::ITEM_PER_PAGE * $page)
            ->setMaxResults(self::ITEM_PER_PAGE);
        return new Paginator($query, false);

    }

    /**
     * Get all products for admin
     *
     * @return Product[]
     */
    public function getAllProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total products
     *
     * @return int
     */
    public function countProducts(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Delete a product
     *
     * @param int $productId
     * @return bool
     */
    public function deleteProduct(int $productId): bool
    {
        $product = $this->find($productId);
        if ($product) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    /**
     * Save a product (create or update)
     *
     * @param Product $product
     * @return Product
     */
    public function save(Product $product): Product
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return $product;
    }

    /**
     * Create a new product
     *
     * @param string $name
     * @param float $price
     * @param \MyAwesomeWebsite\model\Category $category
     * @param int $rating
     * @param string|null $description
     * @param string|null $imageUrl
     * @param int $stock
     * @return Product
     */
    public function createProduct(
        string $name,
        float $price,
        \MyAwesomeWebsite\model\Category $category,
        int $rating = 0,
        ?string $description = null,
        ?string $imageUrl = null,
        int $stock = 0
    ): Product {
        $product = new Product($name, $price, $category, $rating);
        $product->setDescription($description);
        $product->setImageUrl($imageUrl);
        $product->setStock($stock);
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        return $product;
    }

}
