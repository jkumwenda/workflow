<?php

namespace App\Services;

use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Purchase;
use App\Voucher;
use App\Flow;
use App\FlowDetail;
use App\User;
use App\Unit;
use App\Trail;
// use App\Delegation;

use App\Facades\Requisition;

use App\Notifications\Voucher\VoucherSendNextNotification;
use App\Notifications\Voucher\VoucherTransferNotification;

use Carbon\Carbon;

class VoucherService
{
    public function __construct()
    {
    }

    /**
     * Send Next
     *
     * @param Request $request
     * @param Voucher $voucher
     * @param bool $submit true='submit', false='return'
     */
    public function sendNext($request, Voucher $voucher, bool $submit)
    {
        $next = $this->getNextUsers($voucher, ($submit ? 'next' : 'previous'));

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
        $currentTrail = $voucher->trails()->where('status', 'CHECKING')->first();
        $currentTrail->status = $submit ? 'NORMAL' : 'RETURNED';
        $currentTrail->comment = !empty($request->comment) ? $request->comment : null;
        $currentTrail->transaction_at = Carbon::now();
        $currentTrail->save();

        // add new Trail
        $nextTrail = new Trail([
            'flow_id' => $next['flow']->flow_id,
            'flow_detail_id' => $next['flow']->id,
            'user_id' => !empty($next['user']) ? $next['user']->id : null,
            'status' => $next['flow']->requisition_status_id == config('const.REQUISITION_STATUS.VOUCHER.PAID') ? 'NORMAL' : 'CHECKING',
        ]);
        $voucher->trails()->save($nextTrail);

        // update vouchers
        $voucher->requisition_status_id = $next['flow']->requisition_status_id;
        $voucher->current_user_id = !empty($next['user']) ? $next['user']->id : null;
        $voucher->save();

        // delete notifications (voucher)
        $notifications = $request->user()->notifications()->where([['data->type', 'voucher'], ['data->id', $voucher->id]])->get();
        if($notifications) {
            $notifications->markAsRead();
        }


        //notification & sending mail
        if (!empty($next['user'])) {
            //to next user
            Notification::send($next['user'], new VoucherSendNextNotification($submit ? 'submit' : 'return', $voucher, $next['flow'], $next['user'], $currentTrail));
        }

        return $next;
    }

    /**
     * Create Voucher requisition
     *
     * DB transaction should be in the caller
     *
     * @param Request $request
     * @param Purchase $purchase
     * @return Voucher saved data
     */
    public function create($request, $purchase)
    {
        $moduleId = config('const.MODULE.PROCUREMENT.VOUCHER');
        $requisitionStatusId = config('const.REQUISITION_STATUS.VOUCHER.PREPARE');

        $next = $this->getAssignedAccountant($purchase->procurement->unit_id);
        // Next user should be selected
        if ($request->has('next_user_id') && in_array($request->next_user_id, $next['users']->pluck('id')->all()) !== false) {
            $next['user'] = $next['users']->find($request->next_user_id);
        } else if (count($next['users']) == 1) {
            $next['user'] = $next['users']->get(0);
        } else {
            throw new \Exception('Next user is not requested. Check request');
        }

        $items = $purchase->purchaseItems()->get();
        $total = 0;
        foreach ($items as $item) {
            $total += $item->quantity * $item->amount;
        }

        // (*) DB transaction should be in the caller
        /*
        DB::beginTransaction();
        try {
        */

            //voucher
            $voucher = new Voucher([
                'purchase_id' => $purchase->id,
                'total_amount' => $total,
                'requisition_status_id' => $requisitionStatusId,
                'assigned_accountant_user_id' => $next['user']->id,
                'created_user_id' => Auth::user()->id,
                'current_user_id' => $next['user']->id,
            ]);
            $voucher->save();

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
                'user_id' => $next['user']->id,
                'status' => 'CHECKING',
            ]);
            $voucher->trails()->save($trail);

        // (*) DB transaction should be in the caller
        /*
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
        */

        //notification & sending mail
        //to accountant
        Notification::send($next['user'], new VoucherSendNextNotification('submit', $voucher, $next['flow'], $next['user'], null));

        return $voucher;
    }


    /**
     * Get assigned accountant for the unit
     *
     * @param int $unitId
     * @return Object ['flow' => Flow, 'user' => User]
     */
    public function getAssignedAccountant($unitId)
    {
        $nextFlow = null;
        $nextUser = null;

        // get Flow
        $moduleId = config('const.MODULE.PROCUREMENT.VOUCHER');
        $flow = Flow::where([
            ['module_id', $moduleId],
            ['company_id', null]
        ])->first();

        $nextFlow = FlowDetail::where([
            ['flow_id', $flow->id],
            ['level', 1],
        ])->first();

        if (!empty($nextFlow) && !empty($nextFlow->role_id)) {
            // get user belongs the unit
            $nextUsers = User::whereHas('units', function($query) use ($unitId, $nextFlow) {
                $query->where([
                    ['id', $unitId],
                    ['role_id', $nextFlow->role_id],
                ]);
            })->get();

            if (empty($nextUsers)) {
                //Investigation required (要調査)
                throw new \Exception('Wrong trail');
            }
        }

        return ['flow' => $nextFlow, 'users' => $nextUsers];
    }


    /**
     * get next trail users
     *
     * @param Voucher $voucher
     * @param string $sign next or previous
     * @return Object ['flow' => Flow, 'users' => Array(User)]
     */
    public function getNextUsers($voucher, $sign = 'next')
    {
        $currentFlow = $voucher->trails()->where('status', 'CHECKING')->first();
        $currentFlowDetail = !empty($currentFlow) ? $currentFlow->flowDetail : null;

        $submit = ($sign == 'next' ? true : false);
        $next = Requisition::getNextUsers($submit, $currentFlowDetail, $voucher->purchase->procurement->unit, null);

        if ($submit == false) {
            //previous trail (default selected user)
            $previousTrail = $voucher->trails()->where('status', '!=', 'CHECKING')->orderBy('transaction_at', 'desc')->first();
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
     * @return \Voucher
     */
    public function submit($request, $id, $submit = true)
    {
        $voucher = Voucher::findOrFail($id);
        if ($voucher->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Send next
            $next = $this->sendNext($request, $voucher, $submit);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $voucher;
    }

    /**
     * setBankTax
     *
     * @param VoucherRequisitionRequest $request
     * @param int $id
     * @return \Voucher
     */
    public function setBankTax($request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        // update voucher
        $voucher->expenditure_code = $request->expenditure_code;
        $voucher->excepted_tax = $request->excepted_tax;
        $voucher->withholding_tax_code = $request->withholding_tax_code;
        $voucher->tax_applied = $request->tax_applied;
        $voucher->save();

        return $voucher;
    }

    /**
     * Transfer requisition
     *
     * @param Request $request
     * @param int $id
     * @return \Voucher
     */
    public function transfer($request, $id)
    {
        $voucher = Voucher::findOrFail($id);
        if ($voucher->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $receiver = User::findOrFail($request->receiver_user_id);

        DB::beginTransaction();
        try {
            // update trail
            $currentTrail = $voucher->trails()->where('status', 'CHECKING')->first();
            $currentTrail->status = 'TRANSFERRED';
            $currentTrail->comment = $request->comment;
            $currentTrail->transaction_at = Carbon::now();
            $currentTrail->save();

            // add new Trail
            $nextTrail = new Trail([
                'flow_id' => $currentTrail->flow_id,
                'flow_detail_id' => $currentTrail->flow_detail_id,
                'user_id' => $receiver->id,
                'status' => 'CHECKING',
            ]);
            $voucher->trails()->save($nextTrail);

            // update vouchers
            $voucher->current_user_id = $receiver->id;
            $voucher->save();

            // delete notifications (voucher)
            $notifications = $request->user()->notifications()->where([['data->type', 'voucher'], ['data->id', $voucher->id]])->get();
            if($notifications) {
                $notifications->markAsRead();
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        //notification & sending mail
        //to receiver
        Notification::send($receiver, new VoucherTransferNotification($voucher, Auth::user(), $receiver, $request->comment));

        return $voucher;
    }

    /**
     * Paid and close requisition
     *
     * @param Request $request
     * @param int $id
     * @return \Voucher
     */
    public function paid($request, $id)
    {
        $voucher = Voucher::findOrFail($id);
        if ($voucher->current_user_id != Auth::user()->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Send next
            $next = $this->sendNext($request, $voucher, true);

            // If this route is "LPO"
            if ($voucher->purchase->route == 'LPO') {
                // Automatically order closed

                // send Next
                // $OrderService = new OrderService();
                // $procurementNext = $OrderService->sendNext($request, $voucher->order, true);
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return $voucher;
    }
}