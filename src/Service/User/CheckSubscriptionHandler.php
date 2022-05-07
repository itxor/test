<?php

namespace App\Service\User;

use PhpAmqpLib\Message\AMQPMessage;
use App\Service\Email\EmailService;

class CheckSubscriptionHandler
{
    private  EmailService $emailService;

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
        if (!$dto instanceof SendEmailDTO) {
            // todo: log
            return;
        }

        if (!$this->emailService->isValidEmailByUserId($dto->getUserId())) {
            // todo: log
            return;
        }

        $this->emailService->sendEmail($dto);
    }
}
