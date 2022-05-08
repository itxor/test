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

    public function getUsersWithExpiredSubscribeBatch(int $checkedExpiredAt, int $lastId, int $limit) : array
    {
        $sql = <<<SQL
select user_id 
from users u
join emails e on u.email_id = e.id
where u.is_confirmed = true 
    and e.is_valid = true 
    and u.expired_at < :checkExpiredAt 
    and u.user_id > :lastId 
order by user_id
limit :maxRows;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('checkExpiredAt', $checkedExpiredAt, PDO::PARAM_INT);
        $stmt->bindParam('lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam('maxRows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}