<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GradeSeeder extends Seeder
{
     /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $band1 = [
            ['name' => 'Professor', 'subsistence' => '80000', 'lunch' => '21000'],            
            ['name' => 'G1', 'subsistence' => '80000', 'lunch' => '21000'],            
            ['name' => 'G2', 'subsistence' => '80000', 'lunch' => '21000'],            
            ['name' => 'G3', 'subsistence' => '80000', 'lunch' => '21000'],            
                      
        ];
        $band2 = [
            ['name' => 'Associate Professor', 'subsistence' => '64000', 'lunch' => '17000' ],            
            ['name' => 'Senior Lecturer', 'subsistence' => '64000', 'lunch' => '17000' ],            
            ['name' => 'Associate Professor', 'subsistence' => '64000', 'lunch' => '17000' ], 
            ['name' => 'G4', 'subsistence' => '64000', 'lunch' => '1700'],            
            ['name' => 'G5', 'subsistence' => '64000', 'lunch' => '1700'],            
           
        ];
        $band3 = [
            ['name' => 'H', 'subsistence' => '56000', 'lunch' => '14000' ],            
            ['name' => 'I', 'subsistence' => '56000', 'lunch' => '14000' ],            
            ['name' => 'J', 'subsistence' => '56000', 'lunch' => '14000' ], 
            ['name' => 'Lecturer', 'subsistence' => '56000', 'lunch' => '14000' ],
            ['name' => 'AL', 'subsistence' => '56000', 'lunch' => '14000' ], 
            ['name' => 'cL', 'subsistence' => '56000', 'lunch' => '14000' ], 
            ['name' => 'G6', 'subsistence' => '56000', 'lunch' => '14000'],            
            ['name' => 'G7', 'subsistence' => '56000', 'lunch' => '14000'],            
                                  
        ];
        $band4 = [
            ['name' => 'F', 'subsistence' => '47000', 'lunch' => '11000'],            
            ['name' => 'G', 'subsistence' => '47000', 'lunch' => '11000'],            
            ['name' => 'G8', 'subsistence' => '47000', 'lunch' => '11000'],            
        ];
        $band5 = [
            ['name' => 'E', 'subsistence' => '39000', 'lunch' => '9000'],            
            ['name' => 'D', 'subsistence' => '39000', 'lunch' => '9000'],            
            ['name' => 'Intern', 'subsistence' => '39000', 'lunch' => '9000'],            
        ];

        DB::table('grades')->insert($band1);
        DB::table('grades')->insert($band2);
        DB::table('grades')->insert($band3);
        DB::table('grades')->insert($band4);
        DB::table('grades')->insert($band5);
    }
}
