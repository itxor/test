<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmailsTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(self::create());
    }

    public function down(): void
    {
        $this->execute(self::drop());
    }

   private static function create() : string
   {
       return <<<SQL
create table if not exists emails (
    id bigint not null primary key,
    email varchar(70) not null,
    checked bool default false,
    is_valid bool default false
);

create unique index if not exists "email_idx" on emails using btree (email);
SQL;
   }

   private static function drop() : string
   {
       return <<<SQL
drop table if exists emails;
SQL;
   }
}
