<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FlowDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $details = [
            ['flow_id' => 31, 'level' => 1, 'role_id' => 2, 'requisition_status_id' => 70],
            ['flow_id' => 32, 'level' => 1, 'role_id' => 4, 'requisition_status_id' => 80],
            ['flow_id' => 33, 'level' => 1, 'role_id' => 4, 'requisition_status_id' => 90],
           
        ];

        DB::table('flow_details')->insert($details);
    }
}
