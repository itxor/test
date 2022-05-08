<?php

namespace App\Command;

use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\LogService;
use App\Service\RabbitClient;
use App\Service\User\CheckSubscriptionHandler;
use DateTime;
use Exception;

class UserCheckSubscriptionsConsumer implements CommandInterface
{
    public function execute() : void
    {
        $logger = new LogService();
        $emailService = new EmailService(new EmailRepository());

        try {
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
            $channel->queue_bind($queueName, RabbitClient::USER_EXPIRE_SUBSCRIPTION_EXCHANGE);
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
        } catch (Exception $exception) {
            $msg = sprintf(
                "%s (%s): Ошибка при попытке проверить подписку пользователя: %s\n",
                __METHOD__,
                (new DateTime())->format('Y-m-d H:i:s'),
                $exception->getMessage(),
            );
            echo $msg;

            $logger->getLogger()->error($msg);

            return;
        }
    }
}
