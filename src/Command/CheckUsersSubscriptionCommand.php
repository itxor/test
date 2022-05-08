<?php

namespace App\Command;

use App\Repository\EmailLogRepository;
use App\Repository\EmailRepository;
use App\Repository\UserRepository;
use App\Service\Email\EmailService;
use App\Service\LockService;
use App\Service\LogService;
use App\Service\User\SendEmailDTO;
use App\Service\User\UserService;
use DateTime;
use Exception;

class CheckUsersSubscriptionCommand implements CommandInterface
{
    private const LIMIT = 100000;

    public function execute(): void
    {
        $logger = new LogService();
        $lockService = new LockService();
        $isReleaseLock = true;
        try {
            if ($lockService->isLock(__CLASS__)) {
                echo "Задача уже в работе, пожалуйста, подождите\n";
                $isReleaseLock = false;

                return;
            }
            $lockService->acquire(__CLASS__);

            $userService = new UserService(new UserRepository());
            $emailService = new EmailService(new EmailRepository(), new EmailLogRepository());


            $lastId = 0;
            while (true) {
                $users = $userService->getExpiredThreeDaysUsersBatch(
                    $lastId,
                    self::LIMIT
                );
                if (0 === count($users)) {
                    break;
                }

                foreach ($users as $user) {
                    if (!$emailService->isValidEmailByUserId($user['user_id'])) {
                        continue;
                    }

                    $userService->dispatchExpireSubscriptionMessage(
                        new SendEmailDTO($user['user_id'], $user['email_id'])
                    );
                }

                $lastId = $users[count($users) - 1]['user_id'];
            }
        } catch (Exception $exception) {
            $msg = sprintf(
                "%s (%s): Ошибка проверке подписки пользователей: %s\n",
                __METHOD__,
                (new DateTime())->format('Y-m-d H:i:s'),
                $exception->getMessage(),
            );

            $logger->getLogger()->error($msg);

            return;
        } finally {
            if ($isReleaseLock) {
                $lockService->release(__CLASS__);
            }
        }
    }
}
