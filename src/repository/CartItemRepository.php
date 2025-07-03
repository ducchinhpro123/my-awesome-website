<?php


namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Cart;
use MyAwesomeWebsite\model\CartItem;
use MyAwesomeWebsite\model\Product;
use MyAwesomeWebsite\service\OrmHelper;
use MyAwesomeWebsite\repository\ProductRepository;

class CartItemRepository extends EntityRepository
{
    private EntityManager $entityManager;
    private ProductRepository $productRepository;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(CartItem::class);
        $this->productRepository = new ProductRepository();

        parent::__construct($this->entityManager, $entityMetadata);
    }

    /**
     * Add a cart item
     * @param CartItem $cartItem the object will be used to save it into the database
     */
    public function save(CartItem $cartItem)
    {
        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    }

    /**
     * Remove a cart item
     * @param int $productId The ID of the product to remove.
     * @param int $cartId The ID of the cart.
     */
    public function remove(int $productId, int $cartId)
    {
        $db = $this->createQueryBuilder('ci');
        $query = $db->delete($this->getEntityName(), 'ci')
            ->where('ci.product = :productId')
            ->andWhere('ci.cart = :cartId')
            ->setParameter('productId', $productId)
            ->setParameter('cartId', $cartId)
            ->getQuery();
        return $query->execute(); // return the effected rows
    }
}

?>
