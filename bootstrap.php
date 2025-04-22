<?php
// bootstrap.php
require_once "vendor/autoload.php";
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
// Create a simple "default" Doctrine ORM configuration for Attributes
$paths = array(__DIR__."/src/Entity");
$isDevMode = true;
$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
// configuring the database connection
$connection = DriverManager::getConnection([
    'dbname' => 'clisson231_1',
    'user' => 'clisson231',
    'password' => 'keethieFiel1Oobo',
    'host' => 'mysql-etu.unicaen.fr:3306',
    'driver' => 'pdo_mysql',
], $config);
// obtaining the entity manager
$entityManager = new EntityManager($connection, $config);
?>