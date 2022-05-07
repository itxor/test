<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\Connection;
use App\Service\UserService;

class CheckExpiredUsersCommand
{
    public function execute() : void
    {
        $userRepository = new UserRepository();
        $service = new UserService($userRepository);

        $users = $service->getExpiredUsers();
    }
}
