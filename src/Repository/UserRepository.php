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
select user_id 
from users u
join emails e on u.email_id = e.id
where u.confirmed = true 
    and e.is_valid = true 
    and u.expired_at < $checkedExpiredAt 
SQL;

        $prepareSql = $this->connection->prepare($sql);

        return $prepareSql->fetchAll();
    }

    public function isConfirmed(int $userId) : bool
    {
        $sql = <<<SQL
select confirmed from users where user_id = $userId 
SQL;

        $prepareSql = $this->connection->prepare($sql);

        $isConfirmed = $prepareSql->fetch();

        return (bool)$isConfirmed;
    }

}