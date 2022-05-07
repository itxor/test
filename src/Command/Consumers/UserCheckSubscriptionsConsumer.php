<?php

namespace App\Command\Consumers;

use App\Command\CommandInterface;
use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\RabbitClient;
use App\Service\User\CheckSubscriptionHandler;

class UserCheckSubscriptionsConsumer implements CommandInterface
{
    public function execute() : void
    {
        $emailService = new EmailService(new EmailRepository());

        $connection = RabbitClient::get()->connect();
        $channel = $connection->channel();
        $channel->exchange_declare(
            RabbitClient::USER_EXPIRE_SUBSCRIPTION_EXCHANGE,
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
            new CheckSubscriptionHandler($emailService)
        );

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
