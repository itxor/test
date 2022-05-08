<?php

use App\Service\DotEnvService;
use App\Command;
use App\Service\LogService;

require __DIR__ . '/../vendor/autoload.php';

ini_set('memory_limit', '700M');

$logger = new LogService();

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
    $msg = "Не передана команда первым аргументом";
    echo $msg . PHP_EOL;

    $logger->getLogger()->error($msg);

    return;
}

$path = sprintf('./src/Command/%s.php', $class);
if (!file_exists($path)) {
    $msg = sprintf("Переданная команда %s не найдена!\n", $class);
    echo $msg . PHP_EOL;

    $logger->getLogger()->error($msg);

    return;
}

if (!is_readable($path)) {
    $msg = sprintf("Файл %s недоступен для чтения, выставьте верные права и повторите запуск!\n", $path);
    echo $msg . PHP_EOL;

    $logger->getLogger()->error($msg);

    return;
}

$runner = new Command\Runner($class);

try {
    $runner->run();
} catch (Exception $exception) {
    $msg = sprintf("Ошибка при попытке запустить задачу: %s", $exception->getMessage());
    echo $msg . PHP_EOL;

    $logger->getLogger()->error($msg);
}