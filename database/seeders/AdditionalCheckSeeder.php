<?php

use Illuminate\Database\Seeder;

class AdditionalCheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->line("additional");


        $this->command->line("Delete purchase which has no item");
        $purchases = DB::select('select pr.id from purchases pr left join procurement_items pi on pi.purchase_id = pr.id group by pr.id having count(pi.purchase_id) = 0');
        dump($purchases);
        foreach ($purchases as $purchase) {
            DB::table('orders')->where('purchase_id', $purchase->id)->delete();
            DB::table('purchases')->where('id', $purchase->id)->delete();
        }

        $this->command->line("Fix trail");
        $this->command->line("140: RaPaed  69: iLLINS Project PI FUND");
        // skip compliance officer
        $a = DB::update('update trails set flow_detail_id = ? where trailable_type = \'procurements\' and trailable_id in (select id from procurements where unit_id in (140, 69)) and user_id = ? and flow_detail_id != ? order by trailable_id asc ', [94, 38, 94]);
        dump($a);
        $b = DB::update('update trails set flow_detail_id = ? where trailable_type = \'procurements\' and trailable_id in (select id from procurements where unit_id in (140, 69)) and user_id = ? and flow_detail_id != ? order by trailable_id asc ', [95, 39, 95]);
        dump($b);
        $c = DB::update('update trails set flow_detail_id = ? where trailable_type = \'procurements\' and trailable_id in (select id from procurements where unit_id in (140, 69)) and user_id = ? and flow_detail_id != ? order by trailable_id asc ', [96, 65, 96]);
        dump($c);
        $d = DB::update('update trails set flow_detail_id = ? where trailable_type = \'procurements\' and trailable_id in (select id from procurements where unit_id in (140, 69)) and user_id = ? and flow_detail_id != ? order by trailable_id asc ', [96, 99, 96]);
        dump($d);
        // update requisition_status_id in procurement table
        $e = DB::update('update procurements p, (select tmp.trailable_id as procurement_id, tmp.flow_detail_id, fd.requisition_status_id from (select * from trails where trailable_type = \'procurements\' and trailable_id in (select id from procurements where unit_id in (140, 69)) and status in (\'CHECKING\', \'DELEGATING\') order by trailable_id asc) tmp left join flow_details fd on fd.id = tmp.flow_detail_id ) statuses set p.requisition_status_id = statuses.requisition_status_id where statuses.procurement_id = p.id and p.requisition_status_id != statuses.requisition_status_id');
        dump($e);

    }
}
