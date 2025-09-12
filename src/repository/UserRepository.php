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
}

?>
