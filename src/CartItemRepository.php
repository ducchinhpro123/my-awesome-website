<?php

namespace MyAwesomeWebsite;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Cart;
use MyAwesomeWebsite\model\CartItem;

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

    public function save(CartItem $cartItem)
    {
        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    }

}

?>
