<?php

namespace App\Service;

use App\Repository\UserRepository;
use DateTime;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getExpiredUsers() : array
    {
        $threeDaysExpired = (new DateTime())->modify('-3 days')->getTimestamp();

        return $this
            ->userRepository
            ->getUsersWithExpiredSubscribe($threeDaysExpired);
    }
}
