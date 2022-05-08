<?php

namespace App\Command;

use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\Email\ValidateDTO;
use App\Service\LockService;
use DateTime;
use Exception;

class CheckUsersEmailsCommand implements CommandInterface
{
    public function execute(): void
    {
        $lockService = new LockService();
        $isReleaseLock = true;

        try {
            if ($lockService->isLock(__CLASS__)) {
                echo "Задача уже в работе, пожалуйста, подождите\n";
                $isReleaseLock = false;

                return;
            }
            $lockService->acquire(__CLASS__);

            $emailService = new EmailService(new EmailRepository());

            $lastId = 0;
            $limit = 100000;
            while (true) {
                $emails = $emailService->getNotCheckedEmailsBatch($lastId, $limit);
                if (0 === count($emails)) {
                    break;
                }

                foreach ($emails as $email) {
                    $dto = new ValidateDTO($email['id'], $email['email']);
                    $emailService->dispatchEmailValidateMessage($dto);
                }

                $lastId = $emails[count($emails) - 1]['id'];
            }
        } catch (Exception $exception) {
            echo sprintf(
                "%s (%s): Ошибка при попытке валидации email'ов: %s\n",
                __METHOD__,
                (new DateTime())->format('Y-m-d H:i:s'),
                $exception->getMessage(),
            );

            return;
        } finally {
            if ($isReleaseLock) {
                $lockService->release(__CLASS__);
            }
        }
    }
}
