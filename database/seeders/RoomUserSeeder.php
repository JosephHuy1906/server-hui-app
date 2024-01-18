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
                'user_id' => '4bdc395e-77d4-4602-8e0f-af6bb401560f',
                'status' => 'Đang hoạt động',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'

            ],
            [
                'room_id' => 1,
                'user_id' => 'c691d181-2ddf-4469-8bfb-42c314e52486',
                'status' => 'Đang hoạt động',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'

            ],
            [
                'room_id' => 2,
                'user_id' => '4bdc395e-77d4-4602-8e0f-af6bb401560f',
                'status' => 'Đang hoạt động',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'room_id' => 3,
                'user_id' => '4bdc395e-77d4-4602-8e0f-af6bb401560f',
                'status' => 'Đang hoạt động',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'

            ],
        ];

        DB::table('room_user')->insert($roomUsers);
    }
}
