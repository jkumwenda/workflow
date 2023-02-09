<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = [
            ['name' => 'Sedan'],
            ['name' => 'SUV'],
            ['name' => 'Pickup Truck'],
            ['name' => 'Bus'],
            ['name' => 'Hatchback'],
            ['name' => 'MiniVan'],
            ['name' => 'Van'],
        ];

        DB::table('vehicle_types')->insert($type);
    }
}
