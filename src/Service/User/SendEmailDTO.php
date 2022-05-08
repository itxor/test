<?php

namespace App\Service\User;

class SendEmailDTO
{
    private int $userId;

    private int $emailId;

    public function __construct(int $userId, int $emailId)
    {
        $this->userId = $userId;
        $this->emailId = $emailId;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getEmailId() : int
    {
        return $this->emailId;
    }
}
