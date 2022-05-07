<?php

return [
    "paths" => [
        "migrations" => "src/migrations"
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_environment" => "dev",
        "dev" => [
            "adapter" => "pgsql",
            "host" => $_ENV['DB_PHINX_HOST'],
            "name" => $_ENV['DBNAME'],
            "user" => $_ENV['DBUSER'],
            "pass" => $_ENV['DBPASS'],
            "port" => $_ENV['DBPOST']
        ]
    ]
];

