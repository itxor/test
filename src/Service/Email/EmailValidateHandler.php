<?php

declare(strict_types=1);

namespace App\Service\Email;

use App\Service\LogService;
use PhpAmqpLib\Message\AMQPMessage;

class EmailValidateHandler
{
    private EmailService $emailService;

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
                $this->logger->getLogger()->error('Ошибка при попытке валидации email: не удалось распарсить переданное сообщение');

                return;
            }

            $dto = unserialize($body);
            if (!$dto instanceof ValidateDTO) {
                $this->logger->getLogger()->error('Ошибка при попытке валидации email: переданное dto не соответствует обработчику');

                return;
            }

            // на случай, если email проверили, например, в ручную за время, пока сообщение лежало в очереди
            if ($this->emailService->isValidEmailByUserId($dto->getUserId())) {
                $this->logger->getLogger()->error(
                    sprintf('Ошибка при попытке валидации email: переданный email (%s) уже помечен как валидный', $dto->getEmailId())
                );

                return;
            }

            $this->emailService->validateEmail($dto);
        } catch (\Exception $exception) {
            $this->logger->getLogger()->error($exception->getMessage());
        }
    }
}
