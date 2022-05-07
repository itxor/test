<?php

namespace App\Service\Email;

use PhpAmqpLib\Message\AMQPMessage;

class EmailValidateHandler
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function __invoke(AMQPMessage $message) : void
    {
        $body = $message->body;
        if (!is_string($body)) {
            // todo: log
            return;
        }

        $dto = unserialize($body);
        if (!$dto instanceof ValidateDTO) {
            // todo: log
            return;
        }

        // на случай, если email проверили, например, в ручную за время, пока сообщение лежало в очереди
        if ($this->emailService->isValidEmailByUserId($dto->getUserId())) {
            // todo: log
            return;
        }

        $this->emailService->validateEmail($dto);
    }
}
