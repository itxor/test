<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\EmailLogRepository;
use App\Repository\EmailRepository;
use App\Service\Email\EmailService;
use App\Service\LogService;
use App\Service\RabbitClient;
use App\Service\User\CheckSubscriptionHandler;
use DateTime;
use Exception;

/**
 * Консьюмеры, в отличие от команд, должны висеть в памяти, поэтому для запуска подразумевается
 * использование какой-либо демонизирующей обёртки, например - supervisor.
 * В случае с supervisor будет возможность контролировать не только работу воркера, но и количество.
 */
class UserCheckSubscriptionsConsumer implements CommandInterface
{
    public function execute() : void
    {
        $logger = new LogService();
        $emailService = new EmailService(new EmailRepository(), new EmailLogRepository());

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
                RabbitClient::USER_EXPIRE_SUBSCRIPTION_QUEUE,
                false,
                false,
                false,
                false
            );
            $channel->queue_bind(
                $queueName,
                RabbitClient::USER_EXPIRE_SUBSCRIPTION_EXCHANGE
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
