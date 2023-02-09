<?php

namespace App\Services;

use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Purchase;
use App\Order;
use App\Flow;
use App\FlowDetail;
use App\User;
use App\Unit;
use App\Trail;

// use App\Mail\Procurement\ProcurementToOwnerMail;
use App\Notifications\Order\OrderSendNextNotification;

use Carbon\Carbon;

class OrderService
{
    public function __construct()
    {
    }


    /**
     * save order number
     * (Temporary create order requisition)
     *
     * DB transaction should be in the caller
     *
     * @param OrderRequisitionRequest $request
     * @param int $purchaseId
     * @return Purchase $purchase saved data
     */
    public function savePONumber($request, $purchaseId)
    {
        $moduleId = config('const.MODULE.PROCUREMENT.ORDER');
        $requisitionStatusId = config('const.REQUISITION_STATUS.ORDER.LPO_GENERATION');

        $purchase = Purchase::findOrFail($purchaseId);

        // (*) DB transaction should be in the caller
        /*
        DB::beginTransaction();
        try {
        */


            //order
            $order = new Order([
                'purchase_id' => $purchase->id,
                'po_number' => $request->po_number,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
            ]);
            $order->save();

            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
            ->join('flow_details', function($join) {
                $join->on('flow_details.flow_id', '=', 'flows.id')->where('level', 1);
            })
            ->where([
                ['module_id', $moduleId],
                ['company_id', null]
            ])->first();

            // create new trail
            $trail = new Trail([
                'flow_id' => $flow->id,
                'flow_detail_id' => $flow->flow_detail_id,
                'user_id' => Auth::user()->id,
                'status' => 'CHECKING',
            ]);
            $order->trails()->save($trail);

        // (*) DB transaction should be in the caller
        /*
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        */

        return $order;
    }
}