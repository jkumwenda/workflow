<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\OrderRequisitionRequest;

use App\Purchase;
use App\User;

use App\Facades\Requisition;

use App\Services\ProcurementService;
use App\Services\PurchaseService;
use App\Services\OrderService;
use App\Services\VoucherService;

use App\Mail\RplusSystemAlertMail;


class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('requisition', ['type' => 'purchases']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //purchase
        $purchase = Purchase::findOrFail($id);
        $items = $purchase->purchaseItems()->orderBy('id', 'asc')->get();
        $procurement = $purchase->procurement;
        $documents = $procurement->documents()->where('checked', '0')->get();
        $quotations = $procurement->documents()->where('checked', '1')->get();
        $delegations = $purchase->delegations()->where('status', 'Pending')->get();
        $procurementDelegations = $procurement->delegations()->where('status', 'Pending')->get();
        $canceled = $purchase->canceled()->get();
        $trails = $purchase->trails()->orderBy('created_at', 'asc')->get();
        $procurementTrails = $procurement->trails()->orderBy('created_at', 'asc')->get();
        $messages = $purchase->messages()->get();


        $total = 0;
        foreach ($items as $item) {
            $total += $item->quantity * $item->amount;
        }

        //authorize
        $auth = [
            'action' => $purchase->current_user_id == Auth::user()->id,
            'createPurchase' => Auth::user()->can('purchase_admin') || Auth::user()->can('purchase_edit'),
        ];

        $delegated =  !$procurementDelegations->isEmpty() || !$delegations->isEmpty();
        $currentLevel = $purchase->trails()->latest()->first()->flowDetail->level;
        $ableTo = [
            'submit' => $auth['action'] && !$delegated
                && !in_array($purchase->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.PURCHASE_LPO.LPO_GENERATION'),
                        config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING'),
                        config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.SENDING_PAYMENT'),
                        config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.RECEIVED_RATING'),
                    ]
                ),
            'return' => $auth['action'] && !$delegated && $currentLevel != 1
                && !in_array($purchase->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING'),
                        config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.RECEIVED_RATING'),
                    ]
                ),
            'delegate' => $auth['action'] && !$delegated && $purchase->requisition_status_id == config('const.REQUISITION_STATUS.PURCHASE_LPO.LPO_GENERATION'),
            'setLpoNumber' => $auth['action'] && is_null($purchase->order) && $purchase->requisition_status_id == config('const.REQUISITION_STATUS.PURCHASE_LPO.LPO_GENERATION'),
            'sendPayment' => $auth['action'] && !$delegated && $purchase->requisition_status_id == config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.SENDING_PAYMENT'),
            'finishDelegate' => $auth['action'] && $delegated && !is_null($purchase->order), //only display after generating LPO
            'reset' => $auth['action'] && $auth['createPurchase'] && $currentLevel == 1,
            'rate' => $auth['action']
                && in_array($purchase->requisition_status_id,
                    [
                        config('const.REQUISITION_STATUS.PURCHASE_CHEQUE.RECEIVED_RATING'),
                        config('const.REQUISITION_STATUS.PURCHASE_LPO.RECEIVED_RATING')
                    ]
                ),
            'viewVoucher' => !empty($purchase->voucher)
        ];

        //next/previous users
        $purchaseService = new PurchaseService();
        $next = $purchaseService->getNextUsers($purchase, 'next');
        $previous = $purchaseService->getNextUsers($purchase, 'previous');

        //delegatable Users
            $unit = Auth::user()->units()->where('is_default', 1)->first();
            $userId = Auth::user()->id;

            $defaultUnitUsers = User::whereHas('units', function($query) use ($unit, $userId){
                $query->where('id', $unit->id);
            })->where('id', '!=', $userId)->where('active', '1')->get();
            $defaultUnitUsers = $defaultUnitUsers->pluck('name', 'id');
        

        //Assistant accountant
            $VoucherService = new VoucherService();
            $voucherNext = $VoucherService->getAssignedAccountant($procurement->unit_id);
            if ($voucherNext['users']->isEmpty()) {
                $voucherNext['message'] = 'The accountant is not assigned to this unit. Please contact to ICT helpdesk';
                //Send Message to ICT Member
                $supportMembers = explode(',', env('RPLUS_SUPPORT_MAIL', 'rplus@medcol.mw'));
                Mail::to($supportMembers)->send(new RplusSystemAlertMail(
                    sprintf('The accountant is not assigned to "%s". Please check and set it. This mail is sent when Registrar see the purchase requisition detail.', $procurement->unit->name),
                    [],
                    route('purchase/show', $purchase->id)
                ));

            
        }

        return view('purchase.show',compact(
            'procurement', 'purchase', 'items', 'documents', 'quotations', 'delegations', 'procurementDelegations', 'canceled', 'trails', 'procurementTrails', 'messages',
            'total', 'auth', 'ableTo', 'next', 'previous', 'defaultUnitUsers', 'voucherNext'));
    }


    /**
     * Submit and send requisition to the next  user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->submit($request, $id, true); // 3rd param: true -> submit

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }


    /**
     * Return and send requisition to the previous user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function return(Request $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->submit($request, $id, false); // 3rd param: false -> return

        $request->session()->flash('message', 'Successfully returned to the previous user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }

    /**
     * Reset purchase
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request, $id)
    {
        $procurementId = Purchase::findOrFail($id)->procurement_id;

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->reset([$id]);

        $request->session()->flash('message', 'Successfully reset the purchase requisition');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/show', $procurementId);
    }

    /**
     * Delegate purchase
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delegate(Request $request, $id)
    {
        $PurchaseService = new PurchaseService();
        $procurement = $PurchaseService->delegate($request, $id);

        $request->session()->flash('message', 'Successfully delegated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Finish to delegated task & send message
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function finishDelegate(Request $request, $id)
    {
        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->finishDelegate($request, $id);

        $request->session()->flash('message', 'Successfully sent message');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }


    /**
     * Set LPO Number (save LPO Number, temporary create order requisition)
     *
     * @param OrderRequisitionRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function savePoNumber(OrderRequisitionRequest $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $order = $PurchaseService->savePoNumber($request, $id);

        $request->session()->flash('message', 'Successfully set LPO Number');
        $request->session()->flash('alert-class', 'alert-success');

        return redirect()->back();
    }


    /**
     * Send Payment (create Voucher requisition for cheque)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function sendPayment(Request $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->sendPayment($request, $id);

        $request->session()->flash('message', 'Successfully sent accountant');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }


    /**
     * Rate supplier
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->rate($request, $id);

        $request->session()->flash('message', 'Successfully rated and requisition closed');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }
}
