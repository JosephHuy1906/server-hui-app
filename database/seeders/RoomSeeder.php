<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $room = [
            [
                'title' => 'Hụi Sài gòn',
                'price_room' => '10000000',
                'commission_percentage' => 2.2,
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08'
            ],
            [
                'title' => 'Bảo Lộc',
                'price_room' => '5000000',
                'commission_percentage' => 1.5,
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08'
            ],
            [
                'title' => 'Bảo Lộc vip pro',
                'price_room' => '5500000',
                'commission_percentage' => 1.7,
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08'
            ],
        ];
        DB::table('rooms')->insert($room);
    }
}
