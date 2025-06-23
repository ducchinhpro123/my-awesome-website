<?php

namespace MyAwesomeWebsite;

use Doctrine\ORM\EntityManager;

class OrmHelper
{
    private static EntityManager $entityManager;

    public static function getEntityManager(): EntityManager
    {
        return self::$entityManager;
    }

    public static function setEntityManager(EntityManager $entityManager)
    {
        self::$entityManager = $entityManager;
    }

}

?>

