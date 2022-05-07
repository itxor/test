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
select is_valid 
from users
join emails e on users.email_id = e.id
where user_id = $userId
SQL;

        $prepareSql = $this->connection->prepare($sql);

        $userInfo = $prepareSql->fetch();

        return (bool)$userInfo['is_valid'];
    }

    public function findNotCheckedEmails(): array
    {
        $sql = <<<SQL
select id, email 
from users u
join emails e on e.id = u.email_id
where u.confirmed = true 
    and e.checked = false
SQL;

        return $this
            ->connection
            ->prepare($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}
