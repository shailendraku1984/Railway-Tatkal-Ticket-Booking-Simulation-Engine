<?php

namespace App\Libraries;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

class Doctrine
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $paths = [APPPATH . 'Entities'];

        $isDevMode = true;

        $config = ORMSetup::createAttributeMetadataConfiguration(
            $paths,
            $isDevMode
        );

        $connection = [
            'driver'   => 'pdo_mysql',
            'host'     => '127.0.0.1',
            'port'     => 3306,
            'dbname'   => 'ci4_doctrine_ordering',
            'user'     => 'root',
            'password' => '',
            'charset'  => 'utf8mb4',
        ];

        $connectionParams = DriverManager::getConnection(
            $connection,
            $config
        );

        $this->entityManager = new EntityManager(
            $connectionParams,
            $config
        );
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}