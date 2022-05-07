<?php

namespace App\Repository;

use App\Service\DatabaseConnection;
use PDO;

class UserRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = DatabaseConnection::get()->connect();
    }

    public function getUsersWithExpiredSubscribe(int $checkedExpiredAt) : array
    {
        $sql = <<<SQL
select user_id from users where expired_at < $checkedExpiredAt 
SQL;

        $prepareSql = $this->connection->prepare($sql);

        return $prepareSql->fetchAll();
    }

}