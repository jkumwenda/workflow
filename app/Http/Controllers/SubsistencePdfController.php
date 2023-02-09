<?php

namespace App\Http\Controllers;

use App\Services\SubsistenceService;
use App\Subsistence;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Support\Facades\Auth;

class SubsistencePdfController extends Controller
{
    public function downloadSubsistencePdf($id){

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
                $travellerDays[] = "1";
                $amount[] = $amountPerDay * 1;
            } else {
                $travellerDays[] = $dateInterval->format('%a');
                $amount[] = $amountPerDay * $dateInterval->format('%a');
            }
        }

        $pdf = Pdf::loadView('pdf.subsistence-approval-document', compact(
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
        return $pdf->stream("subsistence-approval-document" . time() . ".pdf");
    }
}
