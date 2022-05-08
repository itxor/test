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

    public function getNotCheckedEmailsBatch(int $lastId, int $limit) : array
    {
        return $this->emailRepository->findNotCheckedEmailsBatch($lastId, $limit);
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
            RabbitClient::EMAIL_VALIDATE_EXCHANGE,
            'fanout',
            false,
            false,
            false
        );

        $msg = new AMQPMessage(serialize($dto));
        $channel->basic_publish($msg, RabbitClient::EMAIL_VALIDATE_EXCHANGE);

        $channel->close();
        $connection->close();
    }

    public function validateEmail(ValidateDTO $dto) : bool
    {
        sleep(20);

        return true;
    }

    public function sendEmail(SendEmailDTO $dto) : void
    {
        // todo: реализовать
        sleep(5);
    }
}
