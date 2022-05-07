<?php

namespace App\Service\User;

class SendEmailDTO
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }
}
