<?php

declare(strict_types=1);

namespace App\Repository;

use App\Service\DatabaseConnection;
use Exception;
use PDO;

class EmailLogRepository
{
    private PDO $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = DatabaseConnection::get()->connect();
    }

    public function addLog(int $userId, int $emailId, int $status) : void
    {
        $sql = <<<SQL
insert into emails_3_day_log (email_id, user_id, status) 
values (:emailId, :userId, :status)
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('emailId', $emailId, PDO::PARAM_INT);
        $stmt->bindParam('userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam('status', $status, PDO::PARAM_INT);
        $stmt->execute();
    }
}