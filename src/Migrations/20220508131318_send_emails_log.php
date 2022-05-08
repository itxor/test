<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SendEmailsLog extends AbstractMigration
{
    public function up() : void
    {
        $this->execute(self::create());
    }

    public function down() : void
    {
        $this->execute(self::drop());
    }

    private static function create() : string
    {
        return <<<SQL
create table if not exists emails_3_day_log (
    id serial not null primary key,
    email_id bigint not null references emails,
    user_id bigint not null references users,
    status smallint not null,
    created_time timestamp default now()
                                            
);

create unique index if not exists "user_id_email_id_idx" on users using btree (user_id, email_id);
SQL;
    }

    private static function drop() : string
    {
        return <<<SQL
drop table if exists emails_3_day_log;
SQL;
    }
}
