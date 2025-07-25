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

    /**
     * Create a new cart and save it into the database
     *
     * @return Cart a new cart object
     */
    public function newCart()
    {
        $user = $this->userRepository->find($_SESSION['user_id']);
        $cart = new Cart($user);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    /**
     * Update cart
     *
     * @param \MyAwesomeWebsite\model\Cart $cart save this
     *
     */
    public function save(Cart $cart)
    {
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    /**
     * Get a cart object
     *
     * @return Cart | null
     */
    public function getCart(): Cart | null
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $userId = $_SESSION['user_id'];

        $cart = $this->findOneBy(["user" => $userId]);
        return $cart;
    }

    /**
     * How many cart items in the existing cart?
     *
     * @return int the number of cart items
     */
    public function getCartNumber()
    {
        $cart = $this->getCart();
        return count($cart->getCartItems());
    }
}

?>
