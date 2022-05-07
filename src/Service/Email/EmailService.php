<?php

namespace App\Service\Email;

use App\Repository\EmailRepository;
use App\Service\RabbitClient;
use App\Service\User\SendEmailDTO;
use PhpAmqpLib\Message\AMQPMessage;

class EmailService
{
    private EmailRepository $emailRepository;

    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    public function isValidEmailByUserId(int $userId) : bool
    {
        return $this->emailRepository->isValidEmailByUserId($userId);
    }

    public function dispatchEmailValidateMessage(ValidateDTO $dto) : void
    {
        $connection = RabbitClient::get()->connect();
        $channel = $connection->channel();

        $channel->exchange_declare(
            RabbitClient::USER_EXPIRE_SUBSCRIPTION_EXCHANGE,
            'fanout',
            false,
            false,
            false
        );

        $msg = new AMQPMessage(serialize($dto));
        $channel->basic_publish($msg, RabbitClient::USER_EXPIRE_SUBSCRIPTION_EXCHANGE);

        $channel->close();
        $connection->close();
    }

    public function sendEmail(SendEmailDTO $dto) : void
    {
        // todo: реализовать
        sleep(5);
    }
}
