<?php

namespace App\Http\Controllers;

use Auth;

use App\User;
use App\Campus;
use App\Travel;
use App\Currency;
use DateTime;
use App\Traveller;
use App\District;
use App\Vehicle;
use App\Supplier;
use App\Transport;


use App\Procurement;
use App\VehicleType;
use App\Facades\Requisition;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\TravelService;

use App\Services\TransportService;
use Illuminate\Support\Facades\App;
use App\Services\SubsistenceService;
use App\Http\Requests\TravelRequisitionRequest;
use App\Http\Requests\ProcurementRequisitionRequest;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        $transport = Transport::findOrFail($id);
        $travel = $transport->travel;
        $requisition = $travel->procurement;
        $subsistence = $travel->subsistence()->get()->first();
        $documents = $travel->documents()->where('checked', '0')->get();
        $quotations = $travel->documents()->where('checked', '1')->get();
        $delegations = $requisition->delegations()->where('status', 'Pending')->get();
        $canceled = $travel->canceled()->get();
        $preferedVehicleType=$travel->vehicle_type_id; 
        $trails = $travel->trails()->orderBy('created_at', 'asc')->get();
        $transportTrails = $transport->trails()->orderBy('created_at', 'asc')->get();
        $subsistenceTrails = $subsistence->trails()->orderBy('created_at', 'asc')->get();
        $messages = $requisition->messages()->orderBy('created_at', 'asc')->get();
        $changes = $requisition->changeLogs()->orderBy('created_at', 'asc')->get();
        $vehicles=Vehicle::where('vehicle_type_id', $preferedVehicleType)->pluck('registration_number','id');
        $poolvehicle=Vehicle::where('unit_id',null)->pluck('registration_number','id');
        
        $drivers = User::whereHas(
            'roles', function($q){
                $q->where('short_name', 'Driver');
            }
        )->get()->pluck('full_name','id');


        //authorize
        $auth = [
            'action' => $transport->current_user_id == Auth::user()->id,
            'sameUnit' => in_array($travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== false,
            'subsistence' => Auth::user()->can('subsistence'),
            'travel' => Auth::user()->can('travel'),
            'transport' => Auth::user()->can('transport'),
    
        ];

        $delegated =  !$delegations->isEmpty();
        $ableTo = [
            'amend' => $auth['action'] && ($auth['sameUnit'] || $auth['travel']),
            'allocateDriver' => $auth['action'] && $auth['transport'] && !$auth['subsistence'] &&!in_array($transport->requisition_status_id,

            [
                config('const.REQUISITION_STATUS.TRANSPORT.APPROVAL')
            ]),
            'submit' => $auth['action'] &&  !$delegated && $auth['transport']&& !in_array($transport->requisition_status_id,

            [
                config('const.REQUISITION_STATUS.TRANSPORT.APPROVAL')
            ]),
            'transport' => $auth['action'] && $auth['transport'] && in_array($transport->requisition_status_id,
            [
                config('const.REQUISITION_STATUS.TRANSPORT.APPROVAL')
            ]),
            'return' => $auth['action'] && $requisition->created_user_id != Auth::user()->id && !$delegated,
            'delegate' => $auth['action'] && $auth['travel'] && !$delegated,
            'delete' => $auth['action'] && $requisition->created_user_id == Auth::user()->id && $trails->count() == 1,
            'cancel' => $canceled->isEmpty() && $trails->count() > 1 && in_array($transport->requisition_status_id,
            [
                config('const.REQUISITION_STATUS.TRANSPORT.TRANSPORT_APPROVAL')
            ]),
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
        $transportService = new TransportService();
        $next = $transportService->getNextUsers($transport, 'next');
        $previous = $transportService->getNextUsers($transport, 'previous');

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
        $date1= $travel->datetime_out;
        $date2= $travel->datetime_in;
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval= $datetime1->diff($datetime2);

        if ($datetime1 == $datetime2){
            $days = 1;
        }else{
            $days = $interval -> format('%a');
        }

        //number of days for each traveller
        $travellerDays = [];
        $amount = [];
        foreach ($travel->travellers as $traveller){
            $departureDate = $traveller->departure_date;
            $returnDate = $traveller->return_date;
            $departureDateTime = new DateTime($departureDate);
            $returnDateTime = new DateTime($returnDate);
            $dateInterval= $departureDateTime->diff($returnDateTime);
            $amountPerDay = $traveller->amount;
            
            if ($departureDateTime == $returnDateTime){
                array_push($travellerDays, "1");
                array_push($amount, $amountPerDay * 1);
            }else{
                array_push($travellerDays, $dateInterval -> format('%a'));
                 array_push($amount, $amountPerDay * $dateInterval -> format('%a'));
            }

            

        }

        return view('transport.show', compact(
            'requisition',
            'travel',
            'transport',
            'subsistence',
            'documents',
            'quotations',
            'delegations',
            'vehicles',
            'drivers',
            'canceled',
            'trails',
            'transportTrails',
            'subsistenceTrails',
            'messages',
            'changes',
            //'departmentVehicle',
            'auth',
            'ableTo',
            'poolvehicle',
            'suppliers',
            'currencies',
            'preferedVehicleType',
            'next',
            'previous',
            'unitUsers',
            'defaultUnitUsers',
            'days',
            'travellerDays',
            'amount'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function submit(Request $request, $id)
    {

        $TransportService = new TransportService();
        $transport = $TransportService->submit($request, $id, true);

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {

        $TransportService = new TransportService();
        $transport = $TransportService->approve($request, $id, true);

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function return(Request $request, $id)
    {

        $TransportService = new TransportService();
        $transport = $TransportService->submit($request, $id, false);

        $request->session()->flash('message', 'Successfully returned to the previous user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function allocate(Request $request,$id)
    {
        $TransportService = new TransportService();
        $transport = $TransportService->allocate($request, $id);

        $request->session()->flash('message', 'Successfully Allocate a vehicle to a travel requisition ');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();


    }
}
