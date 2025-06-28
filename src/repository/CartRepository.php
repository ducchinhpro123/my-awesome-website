<?php

namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Cart;
use MyAwesomeWebsite\service\OrmHelper;
use MyAwesomeWebsite\repository\UserRepository;

class CartRepository extends EntityRepository
{
    private EntityManager $entityManager;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(Cart::class);

        $this->userRepository = new UserRepository();

        parent::__construct($this->entityManager, $entityMetadata);
    }

    public function newCart()
    {
        $user = $this->userRepository->find($_SESSION['user_id']);
        $cart = new Cart($user);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    public function save(Cart $cart)
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    public function getCart()
    {
        $userId = $_SESSION['user']->getId();
        $cart = $this->findOneBy(["user" => $userId]);
        return $cart;
    }

    public function getCartNumber()
    {
        $cart = $this->getCart();
        return count($cart->getCartItems());
    }
}

?>
