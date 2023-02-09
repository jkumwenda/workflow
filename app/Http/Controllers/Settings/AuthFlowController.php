<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Company;
use App\Flow;
use App\FlowDetail;
use App\Role;
use App\Trail;
use App\RequisitionStatus;
use App\Module;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

use Carbon\Carbon;

class AuthFlowController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $modules = config('const.MODULE');
        return view('settings.authflow.index', compact('modules','company'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {

    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {

    }

    /**
    * Display the specified resource.
    *
    * @param  \App\Role  $role
    * @return \Illuminate\Http\Response
    */
    public function show(Role $role)
    {
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Role  $role
    * @return \Illuminate\Http\Response
    */
    public function edit(Role $role)
    {

    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $company_id, $module_id)
    {
        $flow = Flow::where([
            'company_id' => $company_id,
            'module_id' => $module_id
        ])->first();
        $flowDetails = $flow->flowDetails()->whereNull('deleted_at')->orderBy('level', 'asc')->get();

        $roleIds = $request->get('role_id');
        $requisitionStatuses = $request->get('requisition_status_id');
        $oldFlowDetailIds =  $request->get('old_flow_detail_id');

        //Validation
        $deleted = [];
        foreach ($flowDetails as $i => $flowDetail) {
            if ( !in_array($flowDetail->id, $oldFlowDetailIds) ) {
                // has been deleted
                $deleted[] = $flowDetail->id;
            }
        }
        // Check if deleted requisition status have been existed
        if (!empty($deleted)) {
            $existedCount = Trail::whereIn('flow_detail_id', $deleted)->where('status', 'CHECKING')->count();
            if ($existedCount > 0) {
                $request->session()->flash('message', 'The status you wanted to delete have been used by the requisitions.');
                $request->session()->flash('alert-class', 'alert-danger');
                return redirect()->back();
            }
        }


        DB::beginTransaction();
        try {
            $actionDate = Carbon::now();
            //delete all
            $flow->flowDetails()->whereNull('deleted_at')->update(['deleted_at' => $actionDate]);

            //insert all
            foreach ($roleIds as $i => $roleId) {
                $newFlowDetail = new FlowDetail([
                    // 'flow_id' => $flow->id,
                    'level' => $i + 1,
                    'role_id' => $roleId,
                    'requisition_status_id' => $requisitionStatuses[$i],
                    // 'created_at' => $actionDate
                ]);
                $flow->flowDetails()->save($newFlowDetail);

                //replace flow_detail_id if used
                if (!empty($oldFlowDetailIds[$i])) {
                    Trail::where('flow_detail_id', $oldFlowDetailIds[$i])->where('status', 'CHECKING')->update(['flow_detail_id' => $newFlowDetail->id]);
                }
            }

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        $request->session()->flash('message', 'Successfully saved');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('authflow', $company_id);
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Role  $role
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request, Role $role)
    {

    }

    /**
    * get flow details of a particular company and module id.
    *
    * @param    $company_id, $module_id
    * @return \Illuminate\Http\Response
    */
    public function flowDetails(Request $request, $company_id, $module_id)
    {
        $moduleName = Module::getModuleName($module_id);

        $matchThese = ['company_id' => $company_id, 'module_id' => $module_id];
        $company = Company::findOrFail($company_id);
        $flow = Flow::where($matchThese)->first();

        $flowDetails = $flow->flowDetails()->whereNull('deleted_at')->get();

        $roles = Role::pluck('description','id');
        $requisitionStatuses = RequisitionStatus::where('module_id', $module_id)->pluck('name','id');

        return view('settings.authflow.details', compact('moduleName', 'flowDetails','company','roles','requisitionStatuses'));
    }


    /**
     * Get summary on Ajax
     *
    * @param    $company_id
    * @return Json
    */
    public function getSummary(Request $request)
    {
        $company_id = $request->company_id;
        $company = Company::findOrFail($company_id);

        $flowSummary = [];
        $flows = Flow::where('company_id', $company_id)->get();
        foreach ($flows as $flow) {
            $moduleName = Module::getModuleName($flow->module_id);
            $flowDetails = $flow->flowDetails()
                ->leftJoin('roles', 'roles.id', '=', 'flow_details.role_id')
                ->leftJoin('requisition_statuses', 'requisition_statuses.id', '=', 'flow_details.requisition_status_id')
                ->select('level', 'roles.description as role_name', 'requisition_statuses.name as requisition_status_name')->whereNull('deleted_at')->get();
            $flowSummary[$moduleName] = $flowDetails->all();
        }

        return response()->json(compact('company','flowSummary'));

    }
}
