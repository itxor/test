<?php

use App\Service\DotEnvService;

require './vendor/autoload.php';

$envPath = './.env';
if (!file_exists($envPath)) {
    echo "Не найден .env файл!";

    return;
}

try {
    (new DotEnvService())->loadEnv("./.env");
} catch (Exception $exception) {
    $errorMsg = sprintf("Не удалось прочитать переменные окружения: %s", $exception->getMessage());
    echo $errorMsg;

    return;
}