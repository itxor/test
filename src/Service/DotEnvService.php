<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

class DotEnvService
{
    /**
     * @throws Exception
     */
    function loadEnv(string $path) : void
    {
        if (!is_readable($path)) {
            throw new Exception("Файл с переменными окружения не доступен для чтения!");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
