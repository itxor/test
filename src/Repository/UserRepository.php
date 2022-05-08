<?php

namespace App\Repository;

use App\Service\DatabaseConnection;
use DateTime;
use Exception;
use PDO;

class UserRepository
{
    private PDO $connection;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->connection = DatabaseConnection::get()->connect();
    }

    public function getUsersWithThreeDaysExpiredSubscribeBatch(int $lastId, int $limit) : array
    {
        $threeDaysExpiredStart = (new DateTime())->modify('+71 hours')->getTimestamp();
        $threeDaysExpiredEnd = (new DateTime())->modify('+73 days')->getTimestamp();

        $sql = <<<SQL
select u.user_id, e.id as email_id, e.email
from users u
join emails e on u.email_id = e.id
left join emails_3_day_log e3dl on e.id = e3dl.email_id and u.user_id = e3dl.user_id
where u.is_confirmed = true 
    and e.is_valid = true 
    and u.expired_at between :checkExpiredAtStart and :checkExpiredAtEnd
    and u.user_id > :lastId 
    and e3dl.id is null
order by user_id
limit :maxRows;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('checkExpiredAtStart', $threeDaysExpiredStart, PDO::PARAM_INT);
        $stmt->bindParam('checkExpiredAtEnd', $threeDaysExpiredEnd, PDO::PARAM_INT);
        $stmt->bindParam('lastId', $lastId, PDO::PARAM_INT);
        $stmt->bindParam('maxRows', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}