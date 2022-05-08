<?php

namespace App\Service;

use Analog\Handler\File;
use Analog\Logger;
use Exception;

class LogService
{
    private Logger $logger;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $path = getenv('LOG_PATH');
        if (!is_string($path)) {
            throw new Exception('Не задана переменная окружения LOG_PATH');
        }

        $this->logger = new Logger();
        $this->logger->handler(File::init($path));
    }

    public function getLogger() : Logger
    {
        return $this->logger;
    }
}
