<?php

require_once 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Cloudinary\Configuration\Configuration;
use Symfony\Component\Dotenv\Dotenv;


$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$connectionParams = [];

if (!empty($_ENV['PSQL'])) {
    $psql = parse_url($_ENV['PSQL']);
    $connectionParams = [
        'host' => $psql['host'],
        'user' => $psql['user'],
        'password' => $psql['pass'],
        'host' => $psql['host'],
        'port' => $psql['port'] ?? 5432,
        'dbname' => ltrim($psql['path'], '/'),
        'driver' => 'pdo_pgsql',
    ];
} else {
    // Local development
    $connectionParams = [
        'dbname' => $_ENV['MYSQL_DATABASE'],
        'user' => $_ENV['MYSQL_USER'],
        'password' => $_ENV['MYSQL_PASSWORD'],
        'host' => $_ENV['MYSQL_HOST'] ?? 3306,
        'driver' => 'pdo_mysql',
    ];

}

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/src/model'],
    isDevMode: true,
);

$connection = DriverManager::getConnection($connectionParams, $config);
$entityManager = new EntityManager($connection, $config);

// For cloudinary
Configuration::instance("cloudinary://{$_ENV['CLOUNDINARY_KEY']}:{$_ENV['CLOUNDINARY_SECRET']}@{$_ENV['CLOUNDINARY_NAME']}?secure=true");
?>
