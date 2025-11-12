<?php

namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\Order;
use MyAwesomeWebsite\model\User;
use MyAwesomeWebsite\service\OrmHelper;

class OrderRepository extends EntityRepository
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(Order::class);

        parent::__construct($this->entityManager, $entityMetadata);
    }

    /**
     * Create a new order and save it into the database
     *
     * @param User $user
     * @param string $totalAmount
     * @return Order
     */
    public function createOrder(User $user, string $totalAmount): Order
    {
        $order = new Order($user, $totalAmount);
        
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Save or update an order
     *
     * @param Order $order
     */
    public function save(Order $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * Get all orders for a specific user
     *
     * @param int $userId
     * @return array
     */
    public function getUserOrders(int $userId): array
    {
        return $this->findBy(['user' => $userId], ['id' => 'DESC']);
    }
}