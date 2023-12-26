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
                'avatar' => 'https://www.guidevietnam.org/wp-content/uploads/2017/04/Bitexco-Financial-Tower-Sky-Deck-in-Ho-Chi-Minh.jpg',
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'title' => 'Bảo Lộc',
                'price_room' => '5000000',
                'commission_percentage' => 1.5,
                'avatar' => 'https://focusasiatravel.vn/wp-content/uploads/2018/09/Th%C3%A0nh-ph%E1%BB%91-B%E1%BA%A3o-L%E1%BB%99c-768x477.jpg',
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'title' => 'Bảo Lộc vip pro',
                'price_room' => '5500000',
                'commission_percentage' => 1.7,
                'avatar' => 'https://vietchallenge.com/images/tour/ba9532ec3a59358795612f64e6b01e20.jpg',
                'date_start' => '2023-12-25 12:32:08',
                'date_end' => '2023-01-25 12:32:08',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
        ];
        DB::table('rooms')->insert($room);
    }
}
