<?php

namespace App\Command;

use App\Repository\EmailRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailService;
use App\Service\Email\ValidateDTO;
use App\Service\User\SendEmailDTO;
use App\Service\User\UserService;

class CheckUsersSubscriptionCommand implements CommandInterface
{
    public function execute() : void
    {
        $userService = new UserService(new UserRepository());
        $emailService = new EmailService(new EmailRepository());

        $users = $userService->getExpiredUsers();
        if (0 === count($users)) {
            return;
        }

        foreach ($users as $user) {
            if (!$emailService->isValidEmailByUserId($user['user_id'])) {
                $emailService->dispatchEmailValidateMessage(
                    new ValidateDTO($user['user_id'], $user['email'])
                );

                continue;
            }

            $userService->dispatchExpireSubscriptionMessage(
                new SendEmailDTO($user['user_id'])
            );
        }
    }
}
