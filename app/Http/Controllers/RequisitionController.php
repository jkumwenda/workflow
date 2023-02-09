<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\SearchRequisitionRequest;

use App\Facades\Requisition;

use Carbon\Carbon;

class RequisitionController extends Controller
{
    /**
     * Display a listing of the event.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SearchRequisitionRequest $request)
    {
        $requisitions =  Requisition::getList($request);
        $confirmedCount = Requisition::getConfirmedCount();
        $searchLists = Requisition::getSearchLists($request->type);

        $type = $request->type;
        $q = $request->q;
        return view('requisition.index',array_merge(compact('requisitions', 'confirmedCount', 'type', 'q'), $searchLists));
    }

    /**
     * Display all delegations for the login user
     *
     * @return \Illuminate\Http\Response
     */
    public function delegation(SearchRequisitionRequest $request)
    {
        $delegations = Requisition::getDelegationList($request);
        $status = $request->status;

        return view('requisition.delegation', compact('delegations', 'status'));
    }
}
