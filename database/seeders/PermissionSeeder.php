<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $data = [
            ['id' => 8, 'name' => 'travel', 'description' => 'Enable to see/access travel requisitions'],
            ['id' => 9, 'name' => 'subsistence', 'description' => 'Enable to see/access subsistence requisitions'],
            ['id' => 10, 'name' => 'transport', 'description' => 'Enable to see/access transport requisitions'],
        ];

        DB::table('permissions')->insert($data);
    }
}
