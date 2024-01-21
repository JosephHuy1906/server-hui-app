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
                'price_room' => 10000000,
                'commission_percentage' => 3,
                'avatar' => 'https://www.guidevietnam.org/wp-content/uploads/2017/04/Bitexco-Financial-Tower-Sky-Deck-in-Ho-Chi-Minh.jpg',
                'total_user' => 5,
                'payment_time' => 'End day',
                'status' => 'Open',
                'accumulated_amount' => 0,
                'date_room_end' => '2023-12-30 12:35:25',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'title' => 'Bảo Lộc',
                'price_room' => 5000000,
                'commission_percentage' => 3,
                'avatar' => 'https://focusasiatravel.vn/wp-content/uploads/2018/09/Th%C3%A0nh-ph%E1%BB%91-B%E1%BA%A3o-L%E1%BB%99c-768x477.jpg',
                'total_user' => 10,
                'payment_time' => 'End day',
                'status' => 'Open',
                'accumulated_amount' => 0,
                'date_room_end' => '2024-01-04 12:35:25',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'title' => 'Bảo Lộc vip pro',
                'price_room' => 5500000,
                'commission_percentage' => 3,
                'avatar' => 'https://vietchallenge.com/images/tour/ba9532ec3a59358795612f64e6b01e20.jpg',
                'total_user' => 20,
                'payment_time' => 'End of Month',
                'status' => 'Close',
                'accumulated_amount' => 0,
                'date_room_end' => '2024-12-25 12:35:25',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
        ];
        DB::table('rooms')->insert($room);
    }
}
