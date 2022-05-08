<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitClient
{
    public const USER_EXPIRE_SUBSCRIPTION_EXCHANGE = 'user_subscribe_expire';
    public const USER_EXPIRE_SUBSCRIPTION_QUEUE  = 'user_subscribe_expire_queue';

    public const EMAIL_VALIDATE_EXCHANGE = 'email_validate_expire';
    public const EMAIL_VALIDATE_QUEUE = 'email_validate_expire_queue';

    private static ?self $client = null;

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function connect() : AMQPStreamConnection
    {
        $err = null;
        if (false === ($host = getenv('RABBIT_HOST'))) {
            $err = 'Не передана переменная окружения RABBIT_HOST';
        }
        if (false === ($port = getenv('RABBIT_PORT'))) {
            $err = 'Не передана переменная окружения RABBIT_PORT';
        }
        if (false === ($user = getenv('RABBIT_USER'))) {
            $err = 'Не передана переменная окружения RABBIT_USER';
        }
        if (false === ($pass = getenv('RABBIT_PASS'))) {
            $err = 'Не передана переменная окружения RABBIT_PASS';
        }

        if (null !== $err) {
            throw new Exception($err);
        }

        return new AMQPStreamConnection($host, $port, $user, $pass);
    }

    public static function get() : self
    {
        if (null === self::$client) {
            self::$client = new self();
        }

        return self::$client;
    }
}
