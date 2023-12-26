<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $message = [
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-28a0a7fbab94',
                'message' => 'Xin chào mọi người',
                'created_at' => '2023-12-25 12:32:08',
                'updated_at' => '2023-12-25 12:32:08'
            ],
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'message' => 'Chào mọi người',
                'created_at' => '2023-12-25 12:35:08',
                'updated_at' => '2023-12-25 12:35:08'
            ],
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'message' => 'Mình mới tham gia nhóm',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'room_id' => 1,
                'user_id' => 'f2e4bf0c-f756-4b54-aaa2-28a0a7fbab94',
                'message' => 'Mình tham gia được 1 tuần rồi',
                'created_at' => '2023-12-25 12:34:01',
                'updated_at' => '2023-12-25 12:34:01'
            ],
        ];

        DB::table('message')->insert($message);
    }
}
