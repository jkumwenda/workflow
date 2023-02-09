<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RequisitionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            ['id' => 70, 'module_id' => 6, 'name' => 'Travel preparation'],
            ['id' => 71, 'module_id' => 6, 'name' => 'Travel checking'],
            ['id' => 72, 'module_id' => 6, 'name' => 'Subsistence and Transport'],
            ['id' => 73, 'module_id' => 6, 'name' => 'Closed'],
            ['id' => 74, 'module_id' => 6, 'name' => 'Canceled'],
            ['id' => 80, 'module_id' => 7, 'name' => 'Subsistence initialization'],
            ['id' => 81, 'module_id' => 7, 'name' => 'Subsistence approval'],
            ['id' => 82, 'module_id' => 7, 'name' => 'Funds approval'],
            ['id' => 83, 'module_id' => 7, 'name' => 'Approval'],
            ['id' => 84, 'module_id' => 7, 'name' => 'Closed'],
            ['id' => 85, 'module_id' => 7, 'name' => 'Canceled'],
            ['id' => 90, 'module_id' => 8, 'name' => 'Transport initialization'],
            ['id' => 91, 'module_id' => 8, 'name' => 'Transport allocation'],
            ['id' => 92, 'module_id' => 8, 'name' => 'Transport approval'],
            ['id' => 93, 'module_id' => 8, 'name' => 'Approval'],
            ['id' => 94, 'module_id' => 8, 'name' => 'Closed'],
            ['id' => 95, 'module_id' => 8, 'name' => 'Canceled'], 
        ];

        DB::table('requisition_statuses')->insert($statuses);
    }
}
