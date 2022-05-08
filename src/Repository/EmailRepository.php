<?php

namespace App\Repository;

use App\Service\DatabaseConnection;
use Exception;
use PDO;

class EmailRepository
{
    private PDO $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = DatabaseConnection::get()->connect();
    }

    public function isValidEmailByUserId(int $userId): bool
    {
        $sql = <<<SQL
select e.is_valid 
from users u
join emails e on u.email_id = e.id
where u.user_id = :userId
    and u.is_confirmed = true 
    and e.is_valid = true
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function findNotCheckedEmailsBatch(int $lastId, int $limit): array
    {
        $sql = <<<SQL
select e.id, e.email, u.user_id
from users u
join emails e on e.id = u.email_id
where u.is_confirmed = true 
    and e.is_checked = false
    and e.is_valid = false
    and u.user_id > :lastId
order by u.user_id
limit :maxRows;
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam('maxRows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function updateCheckedStatus(int $emailId, bool $isValid) : void
    {
        $sql = <<<SQL
update emails
set is_valid = :isValid, is_checked = true
where id = :id;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('isValid', $isValid, PDO::PARAM_BOOL);
        $stmt->bindParam('id', $emailId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
