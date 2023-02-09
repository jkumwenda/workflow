<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districts = [
            ['name' => 'Balaka'],
            ['name' => 'Blantyre'],
            ['name' => 'Chikwawa'],
            ['name' => 'Chiradzulu'],
            ['name' => 'Chitipa'],
            ['name' => 'Dedza'],
            ['name' => 'Dowa'],
            ['name' => 'Karonga'],
            ['name' => 'Kasungu'],
            ['name' => 'Likoma'],
            ['name' => 'Lilongwe'],
            ['name' => 'Machinga'],
            ['name' => 'Mangochi'],
            ['name' => 'Mchinji'],
            ['name' => 'Mulanje'],
            ['name' => 'Mwanza'],
            ['name' => 'Mzimba'],
            ['name' => 'Mzuzu'],
            ['name' => 'Neno'],
            ['name' => 'Nkhata Bay'],
            ['name' => 'Nkhotakota'],
            ['name' => 'Nsanje'],
            ['name' => 'Ntcheu'],
            ['name' => 'Ntchisi'],
            ['name' => 'Phalombe'],
            ['name' => 'Rumphi'],
            ['name' => 'Salima'],
            ['name' => 'Thyolo'],
            ['name' => 'Zomba'],
            
            
            
        ];

        DB::table('districts')->insert($districts);
    }
}
