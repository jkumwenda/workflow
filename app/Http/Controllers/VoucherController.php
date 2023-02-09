<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\VoucherRequisitionRequest;

use App\Purchase;
use App\User;
use App\Voucher;

use App\Facades\Requisition;

use App\Services\VoucherService;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('requisition', ['type' => 'vouchers']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //voucher
        $voucher = Voucher::findOrFail($id);
        $purchase = $voucher->purchase;
        $items = $purchase->purchaseItems()->orderBy('id', 'asc')->get();
        $procurement = $purchase->procurement;
        $canceled = $purchase->canceled()->get();
        $trails = $voucher->trails()->orderBy('created_at', 'asc')->get();
        $purchaseTrails = $purchase->trails()->orderBy('created_at', 'asc')->get();
        $procurementTrails = $procurement->trails()->orderBy('created_at', 'asc')->get();

        //authorize
        $auth = [
            'action' => $voucher->current_user_id == Auth::user()->id,
            'seeVoucher' => Auth::user()->can('voucher'),
        ];

        $currentLevel = $voucher->trails()->latest()->first()->flowDetail->level;
        $ableTo = [
            'submit' => $auth['action'] && !empty($voucher->expenditure_code)
                && !in_array($voucher->requisition_status_id,
                [
                    config('const.REQUISITION_STATUS.VOUCHER.CHEQUE_PROCESSING'),
                ]),
            'return' => $auth['action'] && $currentLevel != 1,
            'transfer' => $auth['action'],
            'setBank' => $auth['action'] && $voucher->requisition_status_id == config('const.REQUISITION_STATUS.VOUCHER.PREPARE'),
            'paid' => $auth['action'] && $voucher->requisition_status_id == config('const.REQUISITION_STATUS.VOUCHER.CHEQUE_PROCESSING'),
        ];

        //next/previous users
        $VoucherService = new VoucherService();
        $next = $VoucherService->getNextUsers($voucher, 'next');
        $previous = $VoucherService->getNextUsers($voucher, 'previous');

        //Transferable Users
        if ($ableTo['transfer']) {
            $unit = Auth::user()->units()->where('is_default', 1)->first();
            $userId = Auth::user()->id;

            $defaultUnitUsers = User::whereHas('units', function($query) use ($unit, $userId){
                $query->where('id', $unit->id);
            })->where('id', '!=', $userId)->where('active', '1')->get();
            $defaultUnitUsers = $defaultUnitUsers->pluck('name', 'id');
        }

        return view('voucher.show',compact(
            'voucher', 'procurement', 'purchase', 'items', 'canceled', 'trails', 'purchaseTrails', 'procurementTrails',
            'auth', 'ableTo', 'next', 'previous', 'defaultUnitUsers'));
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

        $VoucherService = new VoucherService();
        $purchase = $VoucherService->submit($request, $id, true); // 3rd param: true -> submit

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
        $VoucherService = new VoucherService();
        $purchase = $VoucherService->submit($request, $id, false); // 3rd param: false -> return

        $request->session()->flash('message', 'Successfully returned to the previous user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }


    /**
     * Set expenditure and withholding tax
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function setBankTax(VoucherRequisitionRequest $request, $id)
    {
        $VoucherService = new VoucherService();
        $purchase = $VoucherService->setBankTax($request, $id);

        $request->session()->flash('message', 'Successfully set values');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }


    /**
     * Transfer requisition
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request, $id)
    {
        $VoucherService = new VoucherService();
        $purchase = $VoucherService->transfer($request, $id);

        $request->session()->flash('message', 'Successfully transferred');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }


    /**
     * Paid and close requisition
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function paid(Request $request, $id)
    {
        $VoucherService = new VoucherService();
        $purchase = $VoucherService->paid($request, $id);

        $request->session()->flash('message', 'Successfully closed');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }
}
