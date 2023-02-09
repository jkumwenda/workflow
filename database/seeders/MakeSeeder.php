<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $makes = [
            ['make' => 'Toyota'],
            ['make' => 'Nissan'],
            ['make' => 'Ford'],
            ['make' => 'Mazda'],
            ['make' => 'Kia'],
            ['make' => 'Volkswagen'],
            ['make' => 'Chevrolet'],
            ['make' => 'Audi'],
            ['make' => 'BMW'],
            ['make' => 'Honda'],
            ['make' => 'Mercedes-Benz'],
            ['make' => 'Lexus'],
            ['make' => 'Porsche'],
            
        ];

        DB::table('makes')->insert($makes);
    }
}
