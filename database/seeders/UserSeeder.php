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
                'name' => 'Nguyễn Quang Huy',
                'password' => '123456',
                'cccd_before' => 'hah.png',
                'cccd_after' => '1212.png',
                'address' => '771 Nguyễn kiệm, gò vấp',
                'birthday' => '19-09-2000',
                'sex' => 'Nam',
                'avatar' => 'https://scontent.fsgn8-4.fna.fbcdn.net/v/t1.6435-1/119944280_353123582489725_6987951845100911428_n.jpg?stp=dst-jpg_p320x320&_nc_cat=102&ccb=1-7&_nc_sid=2b6aad&_nc_eui2=AeGaUHsrUwVyBh0iBvEW8E14t1M_ShnlqRa3Uz9KGeWpFheRiyaaGWKiq-k-HfKEqVyOzWqkus6FwrnJjTONZGzx&_nc_ohc=UYxAFCdJW3sAX9bk7qq&_nc_ht=scontent.fsgn8-4.fna&oh=00_AfAQA41HaBNkY19ydrsglnwxNhuepaoqfFYnKfm63SH9Pw&oe=65B1CDD7',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
            [
                'id' => 'f2e4bf0c-f756-4b54-aaa2-25460a7fbab7',
                'email' => 'thang@gmail.com',
                'name' => 'Đỗ Mạnh Thắng',
                'password' => '123456',
                'cccd_before' => 'after.png',
                'cccd_after' => 'before.png',
                'address' => 'Bảo lộc',
                'birthday' => '19-09-2000',
                'sex' => 'Nam',
                'avatar' => 'https://scontent.fsgn8-3.fna.fbcdn.net/v/t39.30808-1/391721069_1002742034317148_5126231418563570653_n.jpg?stp=dst-jpg_p320x320&_nc_cat=106&ccb=1-7&_nc_sid=5740b7&_nc_eui2=AeGdmH89cC4pFTKXeWZfKqOoJdIbpchE8RIl0hulyETxEozy5M851QWLQ02u4HjSHJzmYmvVFgj3fnYDINNVYvG3&_nc_ohc=MHjfF07Upw4AX_EsBOs&_nc_oc=AQm4O0vLK_jsLXCc7nX_1ZyfPolSCcQG5Gi5gKqqgSryfEiOiHLyV4SZeE_9zwF7zCVL_VzKw6NejLQycDVhqaM-&_nc_ht=scontent.fsgn8-3.fna&oh=00_AfDioqJtz4yoRmWxG1q7ulvHc8hgF3fGCCDjfgn3b5_w1w&oe=658F5EE5',
                'created_at' => '2023-12-25 12:35:25',
                'updated_at' => '2023-12-25 12:35:25'
            ],
        ];
        DB::table('users')->insert($user);
    }
}
