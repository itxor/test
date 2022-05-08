<?php

use Faker\Factory;
use Phinx\Seed\AbstractSeed;

class TestSeed extends AbstractSeed
{
    public function run()
    {
        $faker = Faker\Factory::create();

        $mocks = [];
        $validExpired = (new DateTime)->modify('+72 hours')->getTimestamp();
        $users = $this->table('users');
        $emails = $this->table('emails');

        for ($i = 0; $i < 100; $i++) {
            $mocks[$i] = [
                'email' => [
                    'email' => $faker->email,
                    'is_checked' => 0 === $i % 2,
                    'is_valid' => 0 === $i % 3,
                ],
                'user' => [
                    'username' => $faker->userName,
                    'expired_at' => 0 === $i % 2 ? $validExpired : (new DateTime())->getTimestamp(),
                    'is_confirmed' => !(0 === $i % 10),
                ],
            ];
        }

        foreach ($mocks as $mock) {
            $emails
                ->insert([$mock['email']])
                ->saveData();
        }

        $ids = $this->fetchAll("
            select id
            from emails
        ");

        $i = 0;
        foreach ($ids as $id) {
            $mocks[$i]['user']['email_id'] = $id['id'];
            $users
                ->insert([$mocks[$i]['user']])
                ->saveData();
            $i++;
        }
    }
}
