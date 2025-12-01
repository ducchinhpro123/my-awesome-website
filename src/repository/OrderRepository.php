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

    /**
     * Get all orders with pagination
     */
    public function getAllOrders(int $page = 0, int $limit = 20): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->setFirstResult($page * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total orders
     */
    public function countOrders(): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count orders by status
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue(): float
    {
        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)')
            ->where('o.status = :status')
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getSingleScalarResult();
        
        return (float) ($result ?? 0);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $order = $this->find($orderId);
        if (!$order) {
            return false;
        }
        $order->setStatus($status);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return true;
    }
}