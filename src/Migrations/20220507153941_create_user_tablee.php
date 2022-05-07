<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserTablee extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(self::create());
    }

    public function down(): void
    {
        $this->execute(self::drop());
    }

    private static function create(): string
    {
        return <<<SQL
create table if not exists users (
    user_id bigint not null primary key,
    username varchar(255) not null,
    email_id bigint not null references emails,
    expired_at bigint,
    is_admin bool default false,
    confirmed bool default false
);

create unique index if not exists "user_id_email_id_idx" on users using btree (user_id, email_id);
SQL;
    }

    private static function drop(): string
    {
        return <<<SQL
drop table if exists users;
SQL;
    }
}
