<?php

namespace App\Service;

use PDO;

class DatabaseConnection
{
    private static ?self $connection = null;

    private function __construct()
    {
    }

    public function connect(): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
            getenv('DBHOST'),
            getenv('DBPOST'),
            getenv('DBNAME'),
            getenv('DBUSER'),
            getenv('DBPASS')
        );
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get() : self
    {
        if (null !== self::$connection) {
            self::$connection = new self();
        }

        return self::$connection;
    }
}