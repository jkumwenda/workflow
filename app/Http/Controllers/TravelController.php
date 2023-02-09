<?php

namespace App\Http\Controllers;


use Auth;
use \Notification;
use App\User;
use DateTime;
use App\Campus;
use App\Travel;
use App\Vehicle;
use App\Currency;
use Illuminate\Support\Facades\Storage;
use App\District;

use App\Supplier;
use App\Procurement;


use App\Subsistence;
use App\VehicleType;
use App\Facades\Requisition;
use Illuminate\Http\Request;
use App\Services\TravelService;
use App\Services\TransportService;

use Illuminate\Support\Facades\App;
use App\Services\ProcurementService;
use App\Services\SubsistenceService;
use App\Mail\Travel\TravelToOwnerMail;
use App\Http\Requests\UploadDocumentRequest;
use App\Http\Requests\TravelRequisitionRequest;
use App\Http\Requests\ProcurementRequisitionRequest;

class TravelController extends Controller
{
    public $accommodationProvided = [
        'Yes' => 'Yes',
        'No' => 'No',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('requisition', ['type' => 'travels']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $units = Requisition::getCreatableRequisitionUnits(config('const.MODULE.TRAVEL.TRAVEL'));
        if ($units->count() == 0) {
            return abort(403, 'You don\'t have permission to create a new procurement requisition');
        }
        $users = User::all()->pluck('full_name', 'id');
        $campuses = Campus::all()->pluck('name', 'id');
        $districts = District::all()->pluck('name', 'id');
        $unitVehicles = Vehicle::with('vehicleType')->where('unit_id', !NULL)->get();
        $poolVehicles = VehicleType::has('vehicle')->pluck('name', 'id');
        $accommodationProvided = $this->accommodationProvided;

        return view('travel.create', compact(
            'units',
            'users',
            'unitVehicles',
            'poolVehicles',
            'districts',
            'accommodationProvided',
            'campuses',
            'districts'
        ));
        /* @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    }
    public function store(TravelRequisitionRequest $request)
    {

        $request->validate([
            'title' => ['required', 'max:255'],
            'purpose' => ['required'],
            'datetime_out' => ['required,date,after:yesterday'],
            'datetime_in' => ['required,date,after:yesterday'],
        ]);

        $TravelService = new TravelService();
        $travel = $TravelService->create($request);

        if ($travel['id']) {
            $request->session()->flash('message', 'Successfully saved');
            $request->session()->flash('alert-class', 'alert-success');
            return redirect()->route('travel/show', $travel->id);
        } else {
            $request->session()->flash('message', $travel['traveller'].' has no grade, contact ICT help desk');
            $request->session()->flash('alert-class', 'alert-danger');
            return back()->withInput();
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
        //travel
        $travel = Travel::findOrFail($id);
        $requisition = $travel->procurement;
        $transport = $travel->transport()->get()->first();
        $subsistence = $travel->subsistence()->get()->first();
        $documents = $travel->documents()->where('checked', '0')->get();

        //$filename=$travel->documents()->where('checked', '0')->pluck('file_name');


        //$url = Storage::url("uploads".$filename[0]);

        $preferedVehicleType = $travel->vehicle_type_id;
        $quotations = $travel->documents()->where('checked', '1')->get();
        $delegations = $requisition->delegations()->where('status', 'Pending')->get();
        $canceled = $requisition->canceled()->get();
        $trails = $travel->trails()->orderBy('created_at', 'asc')->get();
        $messages = $requisition->messages()->orderBy('created_at', 'asc')->get();
        $changes = $requisition->changeLogs()->orderBy('created_at', 'asc')->get();

        //authorize
        $auth = [
            'action' => $travel->current_user_id == Auth::user()->id,
            'sameUnit' => in_array($travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== false,
            'travel' => Auth::user()->can('travel'),
        ];

        $delegated =  !$delegations->isEmpty();
        $ableTo = [
            'amend' => $auth['action'] && ($auth['sameUnit'] || $auth['travel']),
            'submit' => $auth['action'] && !$auth['travel'] && !$delegated,
            'return' => $auth['action'] && $requisition->created_user_id != Auth::user()->id && !$delegated && !$transport,
            'delegate' => $auth['action'] && $auth['travel'] && !$delegated,
            'delete' => $auth['action'] && $requisition->created_user_id == Auth::user()->id && $trails->count() == 1,
            'cancel' => $canceled->isEmpty() && $auth['sameUnit'] && $trails->count() > 1,
            'travel' => $auth['action'] && $auth['travel'] && !$transport && !$subsistence,
            'finishDelegate' => $auth['action'] && $auth['travel'] && $delegated && $delegations[0]->receiver_user_id == Auth::user()->id,
            'uploadQuotations' => $auth['action'] && $auth['travel'] && $quotations->count() < 10,
            'changeOwner' => $auth['sameUnit'],
            'archive' => Auth::user()->can('admin') && $requisition->archived == false,
            'unarchive' => Auth::user()->can('admin') && $requisition->archived == true,
        ];

        //masters
        $suppliers = Supplier::pluck('name', 'id')->all();
        $currencies = Currency::getCurrencies();

        //next/previous users
        $travelService = new TravelService();
        $next = $travelService->getNextUsers($travel, 'next');
        $previous = $travelService->getNextUsers($travel, 'previous');

        //Same Unit Users (for changeOwner)
        $unitUsers = User::whereHas('units', function ($query) use ($requisition) {
            $query->where('id', $requisition->unit_id);
        })->where('id', '!=', Auth::user()->id)->where('active', '1')->get();
        $unitUsers = $unitUsers->pluck('name', 'id');

        //Default unit's users (for delegate)
        $defaultUnitUsers = User::whereHas('units', function ($query) {
            $unit = Auth::user()->units()->where('is_default', 1)->first();
            $query->where('id', $unit->id);
        })->where('id', '!=', Auth::user()->id)->where('active', '1')->get();
        $defaultUnitUsers = $defaultUnitUsers->pluck('name', 'id');

        //number of days
        $date1 = $travel->datetime_out;
        $date2 = $travel->datetime_in;
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);

        if ($datetime1 == $datetime2) {
            $days = 1;
        } else {
            $days = $interval->format('%a');
        }

        //number of days for each traveller
        $travellerDays = [];
        $amount = [];
        foreach ($travel->travellers as $traveller) {
            $departureDate = $traveller->departure_date;
            $returnDate = $traveller->return_date;
            $departureDateTime = new DateTime($departureDate);
            $returnDateTime = new DateTime($returnDate);
            $dateInterval = $departureDateTime->diff($returnDateTime);
            $amountPerDay = $traveller->amount;

            if ($departureDateTime == $returnDateTime) {
                array_push($travellerDays, "1");
                array_push($amount, $amountPerDay * 1);
            } else {
                array_push($travellerDays, $dateInterval->format('%a'));
                array_push($amount, $amountPerDay * $dateInterval->format('%a'));
            }
        }



        return view('travel.show', compact(
            'requisition',
            'travel',
            'transport',
            'subsistence',
            'documents',
            'quotations',
            'delegations',
            'canceled',
            'trails',
            'messages',
            //'url',
            'changes',
            'auth',
            'ableTo',
            'suppliers',
            'currencies',
            'next',
            'previous',
            'unitUsers',
            'defaultUnitUsers',
            'days',
            'travellerDays',
            'amount'
        ));
    }

    public function submit(Request $request, $id)
    {

        $TravelService = new TravelService();
        $travel = $TravelService->submit($request, $id, true);

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {

        $travel = Travel::findOrFail($id);
        $approveCheck = $travel->transport()->count() && $travel->subsistence()->count();
        if (!$approveCheck && $travel->requisition_status_id == config('const.REQUISITION_STATUS.TRAVEL.TRAVEL_CHECKING')) {
            // send next level (Procurement requisition)
            $TravelService = new TravelService();
            $TravelService->sendNext($request, $travel, true);

            //Delete notification (procurement)
            //\App\Notification::where([['data->type', 'procurement'], ['data->id', $purchase->procurement->id]])->delete();
        }


        $TransportService = new TransportService();
        $transport = $TransportService->create($request, $id);

        $SubsistenceService = new SubsistenceService();
        $subsistence = $SubsistenceService->create($request, $id);



        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function amend($id)
    {
        $travel = Travel::findOrFail($id);
        $requisition = $travel->procurement;
        $units = Requisition::getCreatableRequisitionUnits(config('const.MODULE.TRAVEL.TRAVEL'));
        $users = User::all()->pluck('full_name', 'id');
        $campuses = Campus::all()->pluck('name', 'id');
        $districts = District::all()->pluck('name', 'id');
        $unitVehicles = Vehicle::with('vehicleType')->where('unit_id', !NULL)->get();
        $poolVehicles = VehicleType::has('vehicle')->pluck('name', 'id');
        $accommodationProvided = $this->accommodationProvided;


        return view('travel.amend', compact(
            'requisition',
            'travel',
            'units',
            'users',
            'unitVehicles',
            'poolVehicles',
            'districts',
            'accommodationProvided',
            'campuses',
            'districts'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TravelRequisitionRequest $request, $id)
    {
        $TravelService = new TravelService();
        $travel = $TravelService->amend($request, $id);

        if ($travel['id']) {
            $request->session()->flash('message', 'Successfully saved');
            $request->session()->flash('alert-class', 'alert-success');
            return redirect()->route('travel/show', $travel->id);
        } else {
            $request->session()->flash('message', $travel['traveller'].' has no grade, contact ICT help desk');
            $request->session()->flash('alert-class', 'alert-danger');
            return back();
        }
    }


    public function return(Request $request, $id)
    {

        $TravelService = new TravelService();
        $travel = $TravelService->submit($request, $id, false);

        $request->session()->flash('message', 'Successfully returned to the previous user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function cancel(Request $request, $id)
    {

        $requisition = Travel::findOrFail($id);
        $ProcurementService = new ProcurementService();
        $procurement = $ProcurementService->cancel($request, $requisition->procurement_id);

        $TravelService = new TravelService();
        $travel = $TravelService->cancel($request, $id);

        if ($requisition->subsistence || $requisition->transport) {
            $SubsistenceService = new SubsistenceService();
            $subsistence = $SubsistenceService->cancel($request, $requisition->subsistence->id);

            $TransportService = new TransportService();
            $transport = $TransportService->cancel($request, $requisition->transport->id);
        }



        $request->session()->flash('message', 'Successfully canceled');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }
    /**
     * Show documents
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function documents($id)
    {
        $travel = Travel::findOrFail($id);
        $requisition = $travel->procurement;
        $documents = $travel->documents()->where('checked', '0')->get();

        return view('travel.documents', compact(
            'requisition',
            'documents',
            'travel'
        ));
    }

    /**
     * Upload document
     *
     * @param UploadDocumentRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function storeDocument(UploadDocumentRequest $request, $id)
    {

        $file = $request->file('file');
        $documentType = $request->document_type;
        $checked = false;

        $TravelService = new TravelService();
        $travel = $TravelService->saveDocument($file, $id, $documentType, $checked);

        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('travel/documents', $travel->id);
    }

    /**
     * Delete quotation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDocument(Request $request, $id)
    {

        $documentId = $request->document_id;

        $TravelService = new TravelService();
        $travel = $TravelService->deleteDocument($id, $documentId);

        $request->session()->flash('message', 'Successfully uploaded');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('travel/documents', $travel->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        $TravelService = new TravelService();
        $travel = $TravelService->delete($request, $id);

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('requisition', ['type' => 'travels']);
    }

    public function destroy($id)
    {
        //
    }
}
