<?php

use App\Service\DotEnvService;
use App\Command;

require __DIR__ . '/../vendor/autoload.php';

ini_set('memory_limit', '700M');

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

$class = $argv[1] ?? null;
if (null === $class) {
    echo "Не передана команда первым аргументом";

    return;
}

$path = sprintf('./src/Command/%s.php', $class);
if (!file_exists($path)) {
    echo "Переданная команда не найдена!\n";

    return;
}

if (!is_readable($path)) {
    echo "Файл недоступен для чтения, выставьте верные права и повторите запуск!\n";

    return;
}

$runner = new Command\Runner($class);
// todo: add try..catch
$runner->run();
