<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = [
            [
                'id' => 'f2e4bf0c-f756-4b54-aaa2-28a0a7fbab94',
                'email' => 'huy@gmail.com',
                'name' => 'Huy',
                'password' => '123456',
                'cccd_before' => 'hah.png',
                'cccd_after' => '1212.png',
                'avatar' => '123.png',
            ],
            [
                'id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'email' => 'thang@gmail.com',
                'name' => 'Thắng',
                'password' => '123456',
                'cccd_before' => 'after.png',
                'cccd_after' => 'before.png',
                'avatar' => '1235.png',
            ],
        ];
        DB::table('users')->insert($user);
    }
}
