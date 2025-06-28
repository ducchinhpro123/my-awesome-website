<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';

session_start();

use MyAwesomeWebsite\Application;
use MyAwesomeWebsite\service\OrmHelper;

OrmHelper::setEntityManager($entityManager);

$app = new Application();
$app->run();

?>
