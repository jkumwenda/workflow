<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequisitionRequest;
use App\Http\Requests\ProcurementRequisitionRequest;
use App\Http\Requests\UploadDocumentRequest;
use Illuminate\Support\Facades\Storage;
use App\Item;
use App\Procurement;
use App\Supplier;
use App\User;
use App\Currency;

use App\Facades\Requisition;

use App\Services\ProcurementService;
use App\Services\PurchaseService;

class ProcurementController extends Controller
{

    public $uoms = [
        'EACH'  => 'EACH',
        'CARTON'=> 'CARTON',
        'PACK'  => 'PACK',
        'CASE'  => 'CASE',
        'PALLET'=> 'PALLET',
        'REAM'  => 'REAM',
        'BALE'  => 'BALE',
        'BOTTLE'  => 'BOTTLE',
        'TUBE'  => 'TUBE',
        'VIALS'  => 'VIALS',
        'LITERS'  => 'LITERS',
        'METERS'  => 'METERS',
        'BOX'   => 'BOX',
        'AMPULES' => 'AMPULES',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('requisition', ['type' => 'procurements']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $units = Requisition::getCreatableRequisitionUnits(config('const.MODULE.PROCUREMENT.PROCUREMENT'));
        if ($units->count() == 0) {
            return abort(403, 'You don\'t have permission to create a new procurement requisition');
        }
        $uoms = $this->uoms;

        return view('procurement.create',compact(
            'units', 'uoms'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProcurementRequisitionRequest $request)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->create($request);

        $request->session()->flash('message', 'Successfully saved');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/show', $procurement->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //procurement
        $procurement = Procurement::findOrFail($id);
        $items = $procurement->procurementItems()->orderBy('purchase_id', 'asc')->orderBy('id', 'asc')->get();
        $documents = $procurement->documents()->where('checked', '0')->get();
        $filename=$procurement->documents()->where('checked', '0')->pluck('file_name');
         
        $quotations = $procurement->documents()->where('checked', '1')->get();
        $delegations = $procurement->delegations()->where('status', 'Pending')->get();
        $canceled = $procurement->canceled()->get();
        $purchases = $procurement->purchases()->get();
        $trails = $procurement->trails()->orderBy('created_at', 'asc')->get();
        $messages = $procurement->messages()->orderBy('created_at', 'asc')->get();
        $changes = $procurement->changeLogs()->orderBy('created_at', 'asc')->get();


        $total = 0;
        $notPurchased = true;
        $allPurchased = true;
        foreach ($items as $item) {
            $total += $item->quantity * $item->amount;
            $notPurchased = $notPurchased && empty($item->purchase_id);     // all items are not purchased, true
            $allPurchased = $allPurchased && !empty($item->purchase_id);    // all items are purchased, true
        }

        //authorize
        $auth = [
            'action' => $procurement->current_user_id == Auth::user()->id,
            'sameUnit' => in_array($procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== false,
            'createPurchase' => Auth::user()->can('purchase_admin') || Auth::user()->can('purchase_edit'),
        ];

        $delegated =  !$delegations->isEmpty();
        $ableTo = [
            'amend' => $auth['action'] && ($auth['sameUnit'] || ($auth['createPurchase'] && !$allPurchased)),
            'submit' => $auth['action'] && !$auth['createPurchase'] && !$delegated,
            'return' => $auth['action'] && $procurement->created_user_id != Auth::user()->id && !$delegated && $notPurchased,
            'delegate' => $auth['action'] && $auth['createPurchase'] && !$delegated && !$allPurchased,
            'delete' => $auth['action'] && $procurement->created_user_id == Auth::user()->id && $trails->count() == 1,
            'cancel' => $canceled->isEmpty() && $auth['sameUnit'] && $trails->count() > 1,
            'createPurchase' => $auth['action'] && $auth['createPurchase'] && !$allPurchased,
            'finishDelegate' => $auth['action'] && $auth['createPurchase'] && $delegated && $delegations[0]->receiver_user_id == Auth::user()->id,
            'uploadQuotations' => $auth['action'] && $auth['createPurchase'] && $quotations->count() < 10,
            'changeOwner' => $auth['sameUnit'],
            'archive' => Auth::user()->can('admin') && $procurement->archived == false,
            'unarchive' => Auth::user()->can('admin') && $procurement->archived == true,
        ];

        //masters
        $suppliers = Supplier::pluck('name', 'id')->all();
        $currencies = Currency::getCurrencies();



        //next/previous users
        $procurementService = new ProcurementService();
        $next = $procurementService->getNextUsers($procurement, 'next');
        $previous = $procurementService->getNextUsers($procurement, 'previous');

        //Same Unit Users (for changeOwner)
        $unitUsers = User::whereHas('units', function($query) use ($procurement) {
            $query->where('id', $procurement->unit_id);
        })->where('id', '!=', Auth::user()->id)->where('active', '1')->get();
        $unitUsers = $unitUsers->pluck('name', 'id');

        //Default unit's users (for delegate)
        $defaultUnitUsers = User::whereHas('units', function($query) {
            $unit = Auth::user()->units()->where('is_default', 1)->first();
            $query->where('id', $unit->id);
        })->where('id', '!=', Auth::user()->id)->where('active', '1')->get();
        $defaultUnitUsers = $defaultUnitUsers->pluck('name', 'id');

        return view('procurement.show',compact(
            'procurement', 'items', 'documents','filename','quotations', 'delegations', 'canceled', 'purchases', 'trails', 'messages', 'changes',
            'total', 'auth', 'ableTo', 'suppliers', 'currencies', 'next', 'previous', 'unitUsers', 'defaultUnitUsers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function amend($id)
    {
        $procurement = Procurement::findOrFail($id);
        $uoms = $this->uoms;

        return view('procurement.amend',compact(
            'procurement', 'uoms'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProcurementRequisitionRequest $request, $id)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->amend($request, $id);

        $request->session()->flash('message', 'Successfully saved');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/show', $procurement->id);
    }

    /**
     * Item search
     *
     * return item list
     *
     * @param Request $request
     * @return json item list
     */
    public function itemSearch(Request $request)
    {
        $search = strtoupper(trim($request->get('name')));
        $result = Item::where('name', 'LIKE', "%$search%")->orderBy('name', 'asc')->pluck('name', 'id')->all();
        return response()->json($result);
    }

    /**
     * Get unit members
     *
     * @param Request $request
     * @return json item list
     */
    public function getUnitMembers(Request $request)
    {
        $unit = Auth::user()->units()->where('is_default', 1)->first();
        $result = User::whereHas('units', function($query) use ($unit){
            $query->where('id', $unit);
        })->where('active', '1')->pluck('id', 'name')->all();
        return response()->json($result);
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

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->submit($request, $id, true);

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

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->submit($request, $id, false);

        $request->session()->flash('message', 'Successfully returned to the previous user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();

    }


    /**
     * Set supplier (save prices, create purchase requisition)
     *
     * @param PurchaseRequisitionRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function savePrices(PurchaseRequisitionRequest $request, $id)
    {

        $PurchaseService = new PurchaseService();
        $purchase = $PurchaseService->create($request, $id);

        $request->session()->flash('message', 'Successfully created the purchase requisition');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/show', $id);
    }


    /**
     * Show quotations
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function quotations($id) {

        $procurement = Procurement::findOrFail($id);
        $quotations = $procurement->documents()->where('checked', '1')->get();

        return view('procurement.quotations',compact(
            'procurement', 'quotations'
        ));

    }


    /**
     * Upload quotation
     *
     * @param UploadDocumentRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function storeQuotation(UploadDocumentRequest $request, $id) {

        $file = $request->file('file');
        $documentType = 'Quotation';
        $checked = true;

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->saveDocument($file, $id, $documentType, $checked);


        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/quotations', $procurement->id);
    }


    /**
     * Delete quotation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteQuotation(Request $request, $id) {

        $documentId = $request->document_id;

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->deleteDocument($id, $documentId);

        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/quotations', $procurement->id);
    }


    /**
     * Show documents
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function documents($id) {

        $procurement = Procurement::findOrFail($id);
        $documents = $procurement->documents()->where('checked', '0')->get();

        return view('procurement.documents',compact(
            'procurement', 'documents'
        ));

    }


    /**
     * Upload document
     *
     * @param UploadDocumentRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function storeDocument(UploadDocumentRequest $request, $id) {

        $file = $request->file('file');
        $documentType = $request->document_type;
        $checked = false;

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->saveDocument($file, $id, $documentType, $checked);

        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/documents', $procurement->id);
    }


    /**
     * Delete quotation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDocument(Request $request, $id) {

        $documentId = $request->document_id;

        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->deleteDocument($id, $documentId);

        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('procurement/documents', $procurement->id);
    }

    /**
     * Delegate procurement
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delegate(Request $request, $id)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->delegate($request, $id);

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
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->finishDelegate($request, $id);

        $request->session()->flash('message', 'Successfully sent message');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Delete procurement
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->delete($request, $id);

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('requisition', ['type' => 'procurements']);
    }

    /**
     * Cancel procurement
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, $id)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->cancel($request, $id);

        $request->session()->flash('message', 'Successfully canceled');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Change Owner of the procurement requisition
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeOwner(Request $request, $id)
    {
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->changeOwner($request, $id);

        $request->session()->flash('message', 'Successfully changed owner');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Archive selected requisitions
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function archive(Request $request)
    {
        $ProcurementService = new ProcurementService();
        $result = $ProcurementService->archive($request);

        if ($result) {
            $request->session()->flash('message', 'Successfully ' . ($request->get('archive_action', true) ? 'archived' : 'unarchived'));
            $request->session()->flash('alert-class', 'alert-success');
        } else {
            $request->session()->flash('error', 'No requisitions selected');
        }
        return redirect()->back();
    }
}
