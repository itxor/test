<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Service\LogService;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Email\EmailService;

class CheckSubscriptionHandler
{
    private  EmailService $emailService;

    private LogService $logger;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
        $this->logger = new LogService();
    }

    public function __invoke(AMQPMessage $message) : void
    {
        try {
            $body = $message->body;
            if (!is_string($body)) {
                $this->logger->getLogger()->error('Ошибка при попытке проверить подписку пользователя: не удалось распарсить тело сообщения');

                return;
            }

            $dto = unserialize($body);
            if (!$dto instanceof SendEmailDTO) {
                $this->logger->getLogger()->error('Ошибка при попытке проверить подписку пользователя: переданное dto не соответствует обработчику');

                return;
            }

            if (!$this->emailService->isValidEmailByUserId($dto->getUserId())) {
                $this->logger->getLogger()->error('Ошибка при попытке проверить подписку пользователя: еmail помечен как невалидный');

                return;
            }

            $this->emailService->sendEmail($dto);
        } catch (Exception $exception) {
            $this->logger->getLogger()->error($exception->getMessage());
        }
    }
}
