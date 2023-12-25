<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = [
            ['name' => 'Admin'],
            ['name' => 'Sub Admin'],
            ['name' =>  'User'],
        ];
        DB::table('role')->insert($role);
    }
}
