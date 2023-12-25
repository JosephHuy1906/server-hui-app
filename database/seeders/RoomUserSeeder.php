<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomUsers = [
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-28a0a7fbab94',
                'joined_at' => 1
            ],
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'joined_at' => 1
            ],
            [
                'room_id' => 2,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'joined_at' => 1
            ],
            [
                'room_id' => 3,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-28a0a7fbab94',
                'joined_at' => 1
            ],
        ];

        DB::table('room_user')->insert($roomUsers);
    }
}
