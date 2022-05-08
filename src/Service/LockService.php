<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Predis\Client;

class LockService
{
    private Client $client;

    public function __construct()
    {
        $host = getenv('REDIS_HOST');

        $this->client = new Client([$host]);
    }

    /**
     * @throws Exception
     */
    public function acquire(string $name): void
    {
        if (null !== $this->client->get($name)) {
            throw new Exception('Блокировка уже установлена!');
        }

        $this->client->set($name, '1');
    }

    public function release(string $name): void
    {
        $this->client->del($name);
    }

    public function isLock(string $name): bool
    {
        return null !== $this->client->get($name);
    }
}