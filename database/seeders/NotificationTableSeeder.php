<?php

use Illuminate\Database\Seeder;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('notifications')->truncate();

        /*************************
         * Notifications
         ************************* */
        $this->command->line("Notifications");
        $olds = DB::connection('mysql_old')->select('select * from tbl_notification where CHECKED != 1');
        $news = [];
        foreach ($olds as $i => $old) {
            $submitType = null;
            $type = null;
            $data = null;
            $title = null;
            $unitId = null;
            $comment = null;
            $notification = null;
            $notificationType = null;

            if (strpos($old->MESSAGE, 'The delegated requisition has be returned') !== false) {
                //finish delegation
                $submitType = 'FinishDelegate';
                if (!empty($old->FK_PRID)) {
                    $type =  'purchase';
                    $data = \App\Purchase::where('id', $old->FK_PRID)->first();
                    $title = $data->procurement->title;
                    $unitId = $data->procurement->unit_id;
                    $comment = str_replace('The delegated purchase requisition has be returned with this message: <br> ', '', $old->MESSAGE);
                    $notificationType = 'App\Notifications\Purchase\PurchaseFinishDelegateNotification';
                } else {
                    $type =  'procurement';
                    $data = \App\Procurement::where('id', $old->FK_REQUISITIONID)->first();
                    $title = $data->title;
                    $unitId = $data->unit_id;
                    $comment = str_replace('The delegated procurement requisition has be returned with this message: <br> ', '', $old->MESSAGE);
                    $notificationType = 'App\Notifications\Procurement\ProcurementFinishDelegateNotification';
                }

            } else if (strpos($old->MESSAGE, 'The requisition,') !== false) {
                //cancel requisition, next user
                $submitType = 'cancel';
                $type = 'procurement';
                $data = \App\Procurement::where('id', $old->FK_REQUISITIONID)->first();
                $title = $data->title;
                $unitId = $data->unitId;
                $comment = substr($old->MESSAGE, strpos($old->MESSAGE, '<br>') + 6);
                $notificationType = 'App\Notifications\Procurement\ProcurementCancelNotification';

            } else if (strpos($old->MESSAGE, 'Your requisition,') !== false) {
                //cancel requisition, owner

                //nothing
                continue;

            } else if (strpos($old->MESSAGE, 'You have a purchase requisition') !== false) {
                if (strpos($old->NOTIFICATION_URL, 'pa/pvr') !== false || strpos($old->NOTIFICATION_URL, 'pa/vr/') !== false) {
                    //voucher send next
                    $submitType = 'submit'; //Can't determine whether "return"
                    $type = 'voucher';
                    $voucherId = strpos($old->NOTIFICATION_URL, 'pa/vr/') !== false ? str_replace('pa/vr/', '', $old->NOTIFICATION_URL) : str_replace('pa/pvr/', '', $old->NOTIFICATION_URL);
                    $data = \App\Voucher::where('id', $voucherId)->first();
                    $title = $data->purchase->procurement->title;
                    $unitId = $data->purchase->procurement->unitId;
                    $comment = '';
                    $notificationType = 'App\Notifications\Voucher\VoucherSendNextNotification';
                } else {
                    // purchase send next
                    $submitType = 'submit'; //Can't determine whether "return"
                    $type = 'purchase';
                    $data = \App\Purchase::where('id', $old->FK_PRID)->first();
                    $title = $data->procurement->title;
                    $unitId = $data->procurement->unitId;
                    $comment = '';
                    $notificationType = 'App\Notifications\Purchase\PurchaseSendNextNotification';
                }

            } else if (strpos($old->MESSAGE, 'You have a requisition') !== false) {
                // procurement send next
                $submitType = 'submit'; //Can't determine whether "return"
                $type = 'procurement';
                $data = \App\Procurement::where('id', $old->FK_REQUISITIONID)->first();
                if (empty($data)) dd($old);
                $title = $data->title;
                $unitId = $data->unitId;
                $comment = '';
                $notificationType = 'App\Notifications\Procurement\ProcurementSendNextNotification';


            } else if (strpos($old->MESSAGE, 'You have a voucher') !== false) {
                // voucher send next
                $submitType = 'submit'; //Can't determine whether "return"
                $type = 'voucher';
                $voucherId = str_replace('pa/pvr/', '', $old->NOTIFICATION_URL);
                $data = \App\Voucher::where('id', $voucherId)->first();
                $title = $data->purchase->procurement->title;
                $unitId = $data->purchase->procurement->unitId;
                $comment = '';
                $notificationType = 'App\Notifications\Voucher\VoucherSendNextNotification';

            } else if ($old->NOTIFICATION_TITLE == 'RPLUS delegated task update') {
                // Ignore
                continue;
            } else {
                // check 要チェック
                dd($old);
            }

            $news[] = [
                'id' => Str::uuid()->toString(),
                'type' => $notificationType,
                'notifiable_type' => 'users',
                'notifiable_id' => $old->FK_USERID,
                'data' => json_encode([
                    'old' => true,
                    'submitType' => $submitType,
                    'type' => $type,
                    'id' => $data->id,
                    // 'unit_id' => $unitId,
                    // 'title' => $title,
                    // 'data' => $data,
                    'comment' => $comment,
                    'notification' => $old->MESSAGE,
                ]),
                'read_at' => null,
                'created_at' => null,
                'updated_at' => null,
            ];
        }
        DB::table('notifications')->insert($news);
    }
}
