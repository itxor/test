<?php

namespace App\Service\User;

use App\Repository\UserRepository;
use App\Service\RabbitClient;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository,
    )
    {
        $this->userRepository = $userRepository;
    }

    public function getExpiredThreeDaysUsersBatch(int $lastId, int $limit) : array
    {
        return $this
            ->userRepository
            ->getUsersWithThreeDaysExpiredSubscribeBatch($lastId, $limit);
    }

    /**
     * @throws Exception
     */
    public function dispatchExpireSubscriptionMessage(SendEmailDTO $dto) : void
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

}
