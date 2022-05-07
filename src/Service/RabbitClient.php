<?php

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitClient
{
    public const USER_EXPIRE_SUBSCRIPTION_EXCHANGE = 'user_subscribe_expire';
    public const EMAIL_VALIDATE_EXCHANGE = 'email_validate_expire';

    private static ?self $client;

    private function __construct()
    {
    }

    public function connect() : AMQPStreamConnection
    {
        $host = getenv('RABBIT_HOST');
        $port = getenv('RABBIT_PORT');
        $user = getenv('RABBIT_USER');
        $pass = getenv('RABBIT_PASS');

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
