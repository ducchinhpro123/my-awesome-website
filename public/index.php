<?php

require_once __DIR__ . '/../vendor/autoload.php';
/* require_once __DIR__ . '/../bootstrap.php'; */

session_start();

use MyAwesomeWebsite\Application;

$app = new Application();
$app->run();

?>
