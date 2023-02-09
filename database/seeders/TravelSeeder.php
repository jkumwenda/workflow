<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Travel;
use DB;

class TravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('travels')->insert
        ([
            'procurement_id' => 3,
            'flow_id' => 1,
            'requisition_status_id' => 1,
            'level' => 1,
            'travel_type' => 'book',
            'purpose' => 'Conduct Training',
            'vehicle_type_id' => 1,
            'datetime_out' => '2021-06-16 15:09:36',
            'datetime_in' => '2021-06-16 15:09:36',
            'origin' => 1,
            'destination' => 1,
            'created_user_id' => 352,
            'updated_user_id' => 352,
            'driver_id' => 1,
            'vehicle_id' => 1,
            'mileage_out' => 1000,
            'mileage_in' => 2000,
            
        ]);
    }
}
