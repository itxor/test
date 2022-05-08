<?php

namespace App\Repository;

use App\Service\DatabaseConnection;
use PDO;

class EmailRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = DatabaseConnection::get()->connect();
    }

    public function isValidEmailByUserId(int $userId): bool
    {
        $sql = <<<SQL
select e.is_valid 
from users
join emails e on users.email_id = e.id
where user_id = $userId
SQL;

        $prepareSql = $this->connection->prepare($sql);

        $userInfo = $prepareSql->fetch();

        return (bool)$userInfo['is_valid'];
    }

    public function findNotCheckedEmailsBatch(int $lastId, int $limit): array
    {
        $sql = <<<SQL
select e.id, e.email 
from users u
join emails e on e.id = u.email_id
where u.is_confirmed = true 
    and e.is_checked = false
    and u.user_id > $lastId
order by u.user_id
limit $limit;
SQL;

        return $this
            ->connection
            ->prepare($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}
