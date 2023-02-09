<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $flow = [
            ['id'=>31,'company_id' => 15, 'module_id' => 6],
            ['id'=>32,'company_id' => 15, 'module_id' => 7],
            ['id'=>33,'company_id' => 15, 'module_id' => 8],
          
        ];

        DB::table('flows')->insert($flow);
    }
}
