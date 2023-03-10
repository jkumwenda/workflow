<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
 
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // AllTableSeeder::class
            RequisitionStatusSeeder::class,
            FlowSeeder::class,
            FlowDetailSeeder::class,
            DistrictSeeder::class,
            CampusSeeder::class,
            MakeSeeder::class,
            VehicleTypeSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
