<?php

namespace MyAwesomeWebsite;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use MyAwesomeWebsite\model\User;

class UserRepository extends EntityRepository
{

    private EntityManager $entityManager;

    public function __construct()
    {
        $this->entityManager = OrmHelper::getEntityManager();
        $entityMetadata = $this->entityManager->getClassMetadata(User::class);
        parent::__construct($this->entityManager, $entityMetadata);
    }

    public function create(User $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

    }
}

?>
