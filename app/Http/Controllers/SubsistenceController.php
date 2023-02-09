<?php

namespace App\Http\Controllers;

use App\Subsistence;

use Auth;

use App\User;
use App\Campus;
use App\Travel;
use App\Currency;

use App\District;
use DateTime;

use App\Supplier;
use App\Procurement;


use App\VehicleType;
use App\Facades\Requisition;
use Illuminate\Http\Request;
use App\Services\TravelService;
use App\Services\TransportService;

use Illuminate\Support\Facades\App;
use App\Services\SubsistenceService;
use App\Http\Requests\TravelRequisitionRequest;
use App\Http\Requests\ProcurementRequisitionRequest;

class SubsistenceController extends Controller
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
        $subsistence = Subsistence::findOrFail($id);
        $travel = $subsistence->travel;
        $requisition = $travel->procurement;
        $transport = $travel->transport()->get()->first();
        $documents = $travel->documents()->where('checked', '0')->get();
        $quotations = $travel->documents()->where('checked', '1')->get();
        $delegations = $requisition->delegations()->where('status', 'Pending')->get();
        $canceled = $travel->canceled()->get();
        $trails = $travel->trails()->orderBy('created_at', 'asc')->get();
        $transportTrails = $transport->trails()->orderBy('created_at', 'asc')->get();
        $subsistenceTrails = $subsistence->trails()->orderBy('created_at', 'asc')->get();
        $messages = $requisition->messages()->orderBy('created_at', 'asc')->get();
        $changes = $requisition->changeLogs()->orderBy('created_at', 'asc')->get();
        $drivers = User::whereHas(
            'roles',
            function ($q) {
                $q->where('short_name', 'Driver');
            }
        )->get()->pluck('full_name', 'id');
        $numberOfTravellers = $travel->travellers->count();


        //authorize
        $auth = [
            'action' => $subsistence->current_user_id == Auth::user()->id,
            'sameUnit' => in_array($travel->procurement->unit_id, Auth::user()->units()->pluck('id')->all()) !== false,
            'travel' => Auth::user()->can('travel'),
            'subsistence' => Auth::user()->can('subsistence'),
        ];

        $delegated =  !$delegations->isEmpty();
        $ableTo = [
            'amend' => $auth['action'] && ($auth['sameUnit'] || $auth['travel'] ),
            'submit' => $auth['action'] &&  !$delegated && $auth['subsistence'] && !in_array(
                $subsistence->requisition_status_id,
                [
                    config('const.REQUISITION_STATUS.SUBSISTENCE.APPROVAL')
                ]
            ),
            'subsistence' => $auth['action'] && $auth['subsistence'] && in_array(
                $subsistence->requisition_status_id,
                [
                    config('const.REQUISITION_STATUS.SUBSISTENCE.APPROVAL')
                ]
            ),
            'return' => $auth['action'] && $requisition->created_user_id != Auth::user()->id && !$delegated,
            'delegate' => $auth['action'] && $auth['travel'] && !$delegated,
            'delete' => $auth['action'] && $requisition->created_user_id == Auth::user()->id && $trails->count() == 1,
            'cancel' => $canceled->isEmpty() && $auth['sameUnit'] && $trails->count() > 1,
            'travel' => $auth['action'] && $auth['travel'] && !$transport,

            'finishDelegate' => $auth['action'] && $auth['travel'] && $delegated && $delegations[0]->receiver_user_id == Auth::user()->id,
            'uploadQuotations' => $auth['action'] && $auth['travel'] && $quotations->count() < 10,
            'changeOwner' => $auth['sameUnit'],
            'archive' => Auth::user()->can('admin') && $requisition->archived == false,
            'unarchive' => Auth::user()->can('admin') && $requisition->archived == true,
        ];


        //next/previous users
        $subsistenceService = new SubsistenceService();
        $next = $subsistenceService->getNextUsers($subsistence, 'next');
        $previous = $subsistenceService->getNextUsers($subsistence, 'previous');


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

        return view('subsistence.show', compact(
            'requisition',
            'travel',
            'transport',
            'subsistence',
            'documents',
            'quotations',
            'delegations',
            'canceled',
            'trails',
            'transportTrails',
            'subsistenceTrails',
            'messages',
            'changes',
            'auth',
            'ableTo',
            'next',
            'previous',
            'unitUsers',
            'defaultUnitUsers',
            'days',
            'travellerDays',
            'amount',
            'drivers',
            'numberOfTravellers'
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

        $SubsistenceService = new SubsistenceService();
        $subsistence = $SubsistenceService->submit($request, $id, true);

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {

        $SubsistenceService = new SubsistenceService();
        $subsistence = $SubsistenceService->approve($request, $id, true);

        $request->session()->flash('message', 'Successfully submitted to the next user');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function return(Request $request, $id)
    {

        $SubsistenceService = new SubsistenceService();
        $subsistence = $SubsistenceService->submit($request, $id, false);

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
}
