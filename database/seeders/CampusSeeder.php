<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $campus = [
            ['name' => 'Mahatma Gandhi', 'district_id' => 2],
            ['name' => 'Kameza', 'district_id' => 2],
            ['name' => 'Queens Central Hospital', 'district_id' => 2],
            ['name' => 'Lilongwe Upper', 'district_id' => 11],
            ['name' => 'Lilongwe Lower', 'district_id' => 11],
            ['name' => 'Mangochi', 'district_id' => 13],
        ];

        DB::table('campuses')->insert($campus);
    }
}
