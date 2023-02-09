<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Role;
use App\Permission;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

use Carbon\Carbon;

class RolePermissionController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = Permission::all();
        return view('settings.rolePermission.index', compact('role', 'permissions'));
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $roleId)
    {
        $permissions = $request->permissions;

        $role = Role::findOrFail($roleId);

        DB::beginTransaction();
        try {
            //delete all
            $role->permissions()->detach();

            //insert all
            $role->permissions()->attach($permissions);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        $request->session()->flash('message', 'Successfully saved');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }
}
