<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\EmailLogRepository;
use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\Email\ValidateDTO;
use App\Service\LockService;
use App\Service\LogService;
use DateTime;
use Exception;

/**
 * Команда подразумевает запуск по крону.
 * Для избежания коллизий, была реализована система блокировок на основе Redis.
 */
class CheckUsersEmailsCommand implements CommandInterface
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

            $emailService = new EmailService(new EmailRepository(), new EmailLogRepository());

            $lastId = 0;
            while (true) {
                $emails = $emailService->getNotCheckedEmailsBatch($lastId, self::LIMIT);
                echo "Количество:" . count($emails) . PHP_EOL;

                if (0 === count($emails)) {
                    break;
                }

                foreach ($emails as $email) {
                    $dto = new ValidateDTO($email['user_id'], $email['id'], $email['email']);
                    $emailService->dispatchEmailValidateMessage($dto);
                }

                $lastId = $emails[count($emails) - 1]['id'];
            }
        } catch (Exception $exception) {
            $msg = sprintf(
                "%s (%s): Ошибка при попытке валидации email'ов: %s\n",
                __METHOD__,
                (new DateTime())->format('Y-m-d H:i:s'),
                $exception->getMessage(),
            );
            echo $msg;
            $logger->getLogger()->error($msg);

            return;
        } finally {
            if ($isReleaseLock) {
                $lockService->release(__CLASS__);
            }
        }
    }
}
