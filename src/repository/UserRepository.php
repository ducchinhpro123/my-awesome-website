<?php


namespace MyAwesomeWebsite\repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use MyAwesomeWebsite\model\User;
use MyAwesomeWebsite\service\OrmHelper;

/**
 * @extends EntityRepository<User>
 */
class UserRepository extends EntityRepository
{

    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(User::class);
        parent::__construct($this->entityManager, $entityMetadata);
    }

    public function create(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function updateAvatar(int $user_id, string $avatar_path)
    {
        $user = $this->find($user_id);
        if (!$user) {
            throw new \Exception("user not found with ID $user_id");
        }
        $user->setAvatar($avatar_path);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers(int $page = 0, int $limit = 20): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult($page * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total users
     */
    public function countUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Delete a user by ID
     */
    public function delete(int $userId): bool
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Search users by username or name
     */
    public function searchUsers(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.username LIKE :query')
            ->orWhere('u.firstName LIKE :query')
            ->orWhere('u.lastName LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
