<?php

namespace App\Service\User;

class SendEmailDTO
{
    private int $userId;

    private int $emailId;

    private string $email;

    public function __construct(
        int $userId,
        int $emailId,
        string $email
    )
    {
        $this->userId = $userId;
        $this->emailId = $emailId;
        $this->email = $email;
    }

    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getEmailId() : int
    {
        return $this->emailId;
    }

    public function getEmail() : string
    {
        return $this->email;
    }
}
