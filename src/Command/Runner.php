<?php

declare(strict_types=1);

namespace App\Command;

use Exception;

class Runner
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @throws Exception
     */
    public function run() : void
    {
        if (!class_exists($class = "\\App\\Command\\" . $this->class)) {
            throw new Exception("Класс не найден!");
        }

        $command = new $class();
        if (!method_exists($command, 'execute')) {
            throw new Exception("Передана неверная команда");
        }

        $command->execute();
    }
}
