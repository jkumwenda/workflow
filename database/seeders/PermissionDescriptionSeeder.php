<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class PermissionDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("Add descriptions to permissions table");

        $data = [
            ['id' => 1, 'name' => 'admin', 'description' => 'Enable to use System settings (add units, users, etc)'],
            ['id' => 2, 'name' => 'preference', 'description' => 'Display "Preference" menu on the header'],
            ['id' => 3, 'name' => 'vendor', 'description' => 'Enable to use Vendor menu in the Preference menu'],
            ['id' => 4, 'name' => 'purchase_admin', 'description' => 'Enable to manage all purchases and enable to create purchase requisitions'],
            ['id' => 5, 'name' => 'purchase_edit', 'description' => 'Enable to create purchase requisitions'],
            ['id' => 6, 'name' => 'voucher', 'description' => 'Enable to see/access vouchers'],
            ['id' => 7, 'name' => 'order', 'description' => 'Enable to see/access order requisitions'],
            ['id' => 8, 'name' => 'travel', 'description' => 'Enable to see/access travel requisitions'],
            ['id' => 9, 'name' => 'subsistence', 'description' => 'Enable to see/access subsistence requisitions'],
            ['id' => 10, 'name' => 'transport', 'description' => 'Enable to see/access transport requisitions'],
        ];
        foreach ($data as $d) {
            DB::table('permissions')->where('id', $d['id'])->update([
                'description' => $d['description'],
            ]);
        }
    }
}
