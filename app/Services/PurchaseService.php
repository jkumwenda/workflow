<?php

namespace App\Services;

use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Procurement;
use App\Purchase;
use App\Flow;
use App\FlowDetail;
use App\User;
use App\Unit;
use App\Trail;
use App\Delegation;
use App\SupplierEvaluation;
use App\Supplier;

use App\Facades\Requisition;

use App\Mail\Procurement\ProcurementToOwnerMail;
use App\Notifications\Purchase\PurchaseSendNextNotification;
use App\Notifications\Purchase\PurchaseDelegateNotification;
use App\Notifications\Purchase\PurchaseFinishDelegateNotification;
use App\Notifications\Purchase\PurchaseRateNotification;

use Carbon\Carbon;

class PurchaseService
{
    public function __construct()
    {
    }

    /**
     * Send Next
     *
     * @param Request $request
     * @param Purchase $purchase
     * @param bool $submit true='submit', false='return'
     */
    public function sendNext($request, Purchase $purchase, bool $submit)
    {
        $next = $this->getNextUsers($purchase, ($submit ? 'next' : 'previous'));

        if (empty($next['users'])) {
            $next['user'] = null;
        } else if (!empty($next['flow']->role_id)) {
            // Next user should be selected
            if ($request->has('next_user_id') && in_array($request->next_user_id, $next['users']->pluck('id')->all()) !== false) {
                $next['user'] = $next['users']->find($request->next_user_id);
            } else if (count($next['users']) == 1) {
                $next['user'] = $next['users']->get(0);
            } else {
                throw new \Exception('Next user is not requested. Check request');
            }
        }


        // update trail
        $currentTrail = $purchase->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => in_array($next['flow']->requisition_status_id, [
                config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.CLOSED'),
                config('const.REQUISITION_STATUS.PURCHASE_LPO.CLOSED')
             ]) ? 'NORMAL' : 'CHECKING',
        ]);
        $purchase->trails()->save($nextTrail);

        // update purchase
        $purchase->requisition_status_id = $next['flow']->requisition_status_id;
        $purchase->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        $purchase->save();

        // delete notification
        //$notifications = $request->user()->notifications()->where([['data->type', 'purchase'], ['data->id', $purchase->id]])->get();
        $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'purchase'")->whereRaw("JSON_VALUE(data, '$.id') = $purchase->id")->get();
        if($notifications) {
            $notifications->markAsRead();
        }


        //notification & sending mail
        //to next user
        if (!empty($next['user'])) {
            if ($next['user']->id != $purchase->procurement->createdUser->id) {
                Notification::send($next['user'], new PurchaseSendNextNotification($submit ? 'submit' : 'return', $purchase, $next['flow'], $next['user'], $currentTrail));
            }
        }

        //to owner (procurement requisition mail, not purchase one)
        if (in_array($next['flow']->requisition_status_id, [
            config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.CLOSED'),
            config('const.REQUISITION_STATUS.PURCHASE_LPO.CLOSED')
        ]) !== false) {
            //Close
            // nothing

        } else if (in_array($next['flow']->requisition_status_id, [
            config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.RECEIVED_RATING'),
            config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING')
        ]) !== false) {
            //Rating
            Notification::send($next['user'], new PurchaseRateNotification($purchase, $next['flow'], $next['user'], $currentTrail));
        } else {
            //Normal
            Mail::to($purchase->procurement->createdUser->email)->send(new ProcurementToOwnerMail($submit ? 'submit' : 'return', $purchase->procurement, $next['flow'], $next['user'], $currentTrail, null));
        }

        return $next;
    }


    /**
     * Create purchase requisition
     *
     * @param PurchaseRequisitionRequest $request
     * @param int $procurementId
     * @return Purchase $purchase saved data
     */
    public function create($request, $procurementId)
    {
        $moduleId = null;
        $requisitionStatusId = null;
        if ($request->route == 'Cheque') {
            $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_CHEQUE');
            $requisitionStatusId = config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.PURCHASE_REQUISITION_GENERATION');
        } else {
            $moduleId = config('const.MODULE.PROCUREMENT.PURCHASE_LPO');
            $requisitionStatusId = config('const.REQUISITION_STATUS.PURCHASE_LPO.PURCHASE_REQUISITION_GENERATION');
        }

        $procurement = Procurement::findOrFail($procurementId);
        $purchase = null;

        DB::beginTransaction();
        try {

            //purchase
            $purchase = new Purchase([
                'procurement_id' => $procurementId,
                'supplier_id' => $request->supplier,
                'route' => $request->route,
                'requisition_status_id' => $requisitionStatusId,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => Auth::user()->id,
            ]);
            $purchase->save();

            // save prices
            $procurementItemIds = $request->get('procurement_item_id');
            $prices = $request->get('price');
            $currencies = $request->get('currency');
            foreach ($prices as $i => $price) {
                if (!is_null($price)) {
                    $procurementItem = $procurement->procurementItems->find($procurementItemIds[$i]);
                    $procurementItem->amount = $price;
                    $procurementItem->currency = $currencies[$i];
                    $procurementItem->purchase_id = $purchase->id;
                    $procurementItem->save();
                }
            }

            // get Flow
            $flow = Flow::select('flows.id', 'flow_details.id as flow_detail_id')
            ->join('flow_details', function($join) {
                $join->on('flow_details.flow_id', '=', 'flows.id')->where('level', 1);
            })
            ->where([
                ['module_id', $moduleId],
                ['company_id', Unit::find($procurement->unit_id)->company_id]
            ])->first();

            // create new trail
            // (*) when finish delegating, change user_id if it's delegated. See ProcurementService::finishDelegate
            $trail = new Trail([
                'flow_id' => $flow->id,
                'flow_detail_id' => $flow->flow_detail_id,
                'user_id' => Auth::user()->id,
                'status' => 'CHECKING',
            ]);
            $purchase->trails()->save($trail);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $purchase;
    }


    /**
     * Reset purchase requisition(s)
     *
     * @param array $ids
     * @return Boolean
     */
    public function reset(array $ids)
    {

        DB::beginTransaction();
        try {

            foreach ($ids as $id) {

                $purchase = Purchase::findOrFail($id);

                //delete purchase_id from procurementItems
                $purchase->purchaseItems()->update([
                    'purchase_id' => null,
                    'amount' => null
                ]);

                //delete all quotations
                $documents = $purchase->procurement->documents()->where('checked', '1')->get();
                foreach ($documents as $document) {
                    Storage::delete('public/' . $document->file_path);
                }

                // Delete from documents table
                $purchase->procurement->documents()->where('checked', '1')->delete();

                //delete purchase
                $purchase->delete();
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return true;
    }


    /**
     * get next selectable trail users
     *
     * @param Purchase $purchase
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($purchase, $sign = 'next')
    {
        $currentFlow = $purchase->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;

        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $purchase->procurement->unit, $purchase->procurement->createdUser);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $purchase->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
            if (!empty($previousTrail)) {
                $next['user'] = User::find($previousTrail->user_id);
            }
        }
        return $next;
    }

    /**
     * Submit
     *
     * @param Request $request
     * @param int $id
     * @param boolean submit type  true = submit, false = return
     * @return \Purchase
     */
    public function submit($request, $id, $submit = true)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // send next
        $next = $this->getNextUsers($purchase, ($submit ? 'next' : 'previous'));

        DB::beginTransaction();
        try {
            //Send Next
            $next = $this->sendNext($request, $purchase, $submit);

            // If All items in the requisition are purchased
            $notPurchasedCount = Procurement::find($purchase->procurement_id)->procurementItems()->whereNull('purchase_id')->count();
            if ($notPurchasedCount == 0 && $purchase->procurement->requisition_status_id == config('const.REQUISITION_STATUS.PROCUREMENT.QUOTATION_SOURCING')) {
                // send next level (Procurement requisition)
                $ProcurementService = new ProcurementService();
                $ProcurementService->sendNext($request, $purchase->procurement, true);

                //Delete notification (procurement)
                \App\Notification::whereRaw("JSON_VALUE(data, '$.type') = 'purchase'")->whereRaw("JSON_VALUE(data, '$.id') = $purchase->id")->delete();
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        return $purchase;
    }

    /**
     * Delegate
     *
     * @param Request $request
     * @param int $id
     * @return \Purchase
     */
    public function delegate($request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $receiver = User::findOrFail($request->receiver_user_id);

        DB::beginTransaction();
        try {
            // insert delegations table
            $newDelegation = new Delegation([
                'status' => 'Pending',
                'sender_user_id' => Auth::user()->id,
                'receiver_user_id' => $receiver->id,
                'requisition_status_id' => $purchase->requisition_status_id,
                'sender_comment' => $request->comment,
                'created_user_id' => Auth::user()->id
            ]);
            $purchase->delegations()->save($newDelegation);

            // change current user of the target requisition
            $purchase->current_user_id = $receiver->id;
            $purchase->save();

            //Update trails status
            $currentTrail = $purchase->trails()->where('status', 'CHECKING')->first();
            $currentTrail->update([
                'status' => 'DELEGATING'
            ]);

            // delete notifications (purchase)
            //$notifications = $request->user()->notifications()->where([['data->type', 'purchase'], ['data->id', $purchase->id]])->get();
            $notifications = $request->user()->notifications()->whereRaw("JSON_VALUE(data, '$.type') = 'purchase'")->whereRaw("JSON_VALUE(data, '$.id') = $purchase->id")->get();
            if($notifications) {
                $notifications->markAsRead();
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }


        //notification & sending mail
        //to receiver
        Notification::send($receiver, new PurchaseDelegateNotification($purchase, Auth::user(), $receiver, $request->comment));

        //to owner (procurement requisition mail, not purchase one)
        Mail::to($purchase->procurement->createdUser->email)->send(new ProcurementToOwnerMail('delegate', $purchase->procurement, null, $receiver, null, null));

        DB::commit();

        return $purchase;
    }

    /**
     * Finish to delegated task and send message
     *
     * @param Request $request
     * @param int $id
     * @return \Purchase
     */
    public function finishDelegate($request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $delegation = $purchase->delegations()->where('status', 'Pending')->first();
        $requester = User::findOrFail($delegation->sender_user_id);

        DB::beginTransaction();
        try {
            // change status on delegations table
            $delegation->status = 'Checked';
            $delegation->checked_at = date("Y-m-d H:i:s");
            $delegation->receiver_comment = $request->comment;
            $delegation->save();

            //Update trails status
            $currentTrail = $purchase->trails()->where('status', 'DELEGATING')->first();
            $currentTrail->update([
                'status' => 'CHECKING'
            ]);

            if (is_null($purchase->order) !== true) {
                // order has been created

                //Send Next **** TEMPORARY **** (TODO)
                $this->sendNext($request, $purchase, true);
            } else {
                 //order has not been created

                // change current user of the target requisition
                $purchase->current_user_id = $requester->id;
                $purchase->save();
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        //notification & sending mail
        //to receiver
        Notification::send($requester, new PurchaseFinishDelegateNotification($purchase, Auth::user(), $requester, $request->comment));

        DB::commit();

        return $purchase;
    }

    /**
     * Set LPO Number (Temporary create order requisition for LPO)
     *
     * (After able to generate order through RPLUS system,
     * please delete this method)
     *
     * @param Request $request
     * @param int $id
     * @return \Purchase
     */
    public function savePoNumber($request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {

            //Add Order requisition
            $OrderService = new OrderService();
            $order = $OrderService->savePONumber($request, $id);

            $delegation = $purchase->delegations()->where('status', 'Pending')->first();
            if (empty($delegation)) {
                //TODO Send Next *** TEMPORARY ****
                //(If PS can generate LPO through RPLUS system, display created order requisition page with "submit" button)
                $next = $this->sendNext($request, $purchase, true);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $order;
    }


    /**
     * Send Payment (Create voucher requisition for cheque)
     *
     * @param Request $request
     * @param int $id
     * @return \Purchase
     */
    public function sendPayment($request, $id)
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {

            //Add Voucher requisition
            $VoucherService = new VoucherService();
            $voucher = $VoucherService->create($request, $purchase);

            //Send Next
            $this->sendNext($request, $purchase, true);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $purchase;
    }

    /**
     * Rate
     *
     * @param Request $request
     * @param int $id
     * @return \Purchase
     */
    public function rate($request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $request->validate([
            'score' => 'required|in:1,2,3,4,5',
            'comment' => 'nullable|string',
        ]);

        $currentTrail = $purchase->trails()->where('status', 'CHECKING')->first();
        if ($currentTrail->user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $next = $this->getNextUsers($purchase, 'next');



        DB::beginTransaction();
        try {

            // Add supplier evaluation
            $supplierEvaluation = new SupplierEvaluation([
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier->id,
                'score' => $request->score,
                'comment' => $request->comment,
                'created_user_id' => Auth::user()->id,
            ]);
            $supplierEvaluation->save();

            // Calculate average
            $calc = SupplierEvaluation::where('supplier_id', $purchase->supplier->id)->select(DB::raw('avg(score) as average, count(id) as cnt'))->first();
            Supplier::where('id', $purchase->supplier->id)->update([
                'score' => $calc->average,
                'evaluation_number' => $calc->cnt
            ]);

            // Send Next
            $next = $this->sendNext($request, $purchase, true);


            // If All items in the requisition are received
            $chequeNotCloseCount = Purchase::where([
                ['procurement_id', $purchase->procurement_id],
                ['route', 'Cheque'],
                ['requisition_status_id', '<>', config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.CLOSED')]
            ])->count();
            $lpoNotCloseCount = Purchase::where([
                ['procurement_id', $purchase->procurement_id],
                ['route', 'LPO'],
                ['requisition_status_id', '<>', config('const.REQUISITION_STATUS.PURCHASE_LPO.CLOSED')]
            ])->count();
            if ($chequeNotCloseCount == 0 && $lpoNotCloseCount == 0) {

                //Send Next (Procurement, closing)
                $ProcurementService = new ProcurementService();
                $procurementNext = $ProcurementService->sendNext($request, $purchase->procurement, true);

            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $purchase;
    }
}