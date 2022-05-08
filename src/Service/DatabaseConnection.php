<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use PDO;

class DatabaseConnection
{
    private static ?self $connection = null;

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function connect(): PDO
    {
        $logService = new LogService();
        $err = null;

        if (false === ($host = getenv('DBHOST'))) {
            $err = "Не задана переменная окружения DBHOST\n";
        }
        if (false === ($port = getenv('DBPORT'))) {
            $err = "Не задана переменная окружения DBPORT\n";
        }
        if (false === ($name = getenv('DBNAME'))) {
            $err = "Не задана переменная окружения DBNAME\n";
        }
        if (false === ($user = getenv('DBUSER'))) {
            $err = "Не задана переменная окружения DBUSER\n";
        }
        if (false === ($pass = getenv('DBPASS'))) {
            $err = "Не задана переменная окружения DBPASS\n";
        }

        if (null !== $err) {
            throw new Exception($err);
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
            $host,
            $port,
            $name,
            $user,
            $pass
        );
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get(): self
    {
        if (null === self::$connection) {
            self::$connection = new self();
        }

        return self::$connection;
    }
}