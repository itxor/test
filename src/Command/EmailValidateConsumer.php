<?php

namespace App\Command;

use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\Email\EmailValidateHandler;
use App\Service\RabbitClient;

class EmailValidateConsumer implements CommandInterface
{
    public function execute() : void
    {
        $emailService = new EmailService(new EmailRepository());

        $connection = RabbitClient::get()->connect();
        $channel = $connection->channel();
        $channel->exchange_declare(
            RabbitClient::EMAIL_VALIDATE_EXCHANGE,
            'fanout',
            false,
            false,
            false
        );

        list($queueName,) = $channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );
        $channel->basic_consume(
            $queueName,
            '',
            false,
            true,
            false,
            false,
            new EmailValidateHandler($emailService)
        );

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
