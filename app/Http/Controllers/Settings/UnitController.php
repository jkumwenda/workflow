<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use DB;

use App\Company;
use App\Unit;
use App\User;
use App\Role;
use Illuminate\Http\Request;

use Illuminate\Database\QueryException;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $units = Unit::get();
      return view('settings.unit.index',compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::pluck('name', 'id');
        return view('settings.unit.create',compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Unit::create($request->all());

        $request->session()->flash('message', 'Successfully created');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('units.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        $companies = Company::pluck('name', 'id');
        return view('settings.unit.edit', compact('unit','companies')) ;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Unit::findOrFail($id)->update($request->all());
        $request->session()->flash('message', 'Successfully updated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('units.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Unit $unit)
    {
        try {
            $unit->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('units.index');
        }

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('units.index');
    }


    /**
     * Show users in the unit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  number $id unit_id
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

       return view('settings.unit.users', compact('unit')) ;
    }


    /**
     * Show Adding user/role page
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  number $id unit_id
     * @return \Illuminate\Http\Response
     */
    public function addUser(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $roles = Role::pluck('description','id');
        $allUsers = User::where('active', '1')->get();
        // $users = $users->pluck('name', 'id');
        $users = [];
        foreach ($allUsers as $user) {
            $users[$user->id] = sprintf('%s (%s)', $user->name, $user->username);
        }

       return view('settings.unit.adduser', compact('roles','users','unit')) ;
    }


    /**
     * Attach user and role for the unit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  number $id unit_id
     * @return \Illuminate\Http\Response
     */
    public function attachUser(Request $request, $id)
    {
        $unit = Unit::findOrFail($request->unit_id);

        $role_exists = DB::table('role_user')->where([
            ['unit_id', '=', $request->unit_id],
            ['role_id', '=', $request->role_id],
        ])->exists();

        $role = Role::findOrFail($request->role_id);
        if($role->single_user == '1' && $role_exists){
            $request->session()->flash('message', 'Can not attach a role.  There already exists a person with that role in the unit');
            $request->session()->flash('alert-class', 'alert-danger');
            // return redirect()->route('unit.users',$request->unit_id);
            return redirect()->back()->withInput($request->input());
        } else {
            try {
                $unit->users()->attach($request->user_id,['role_id' => $request->role_id,'is_default'=>'0']);
            } catch (QueryException $e) {
                $request->session()->flash('message', 'Can not attach a role. Please check foreign data or if a role is a single user');
                $request->session()->flash('alert-class', 'alert-danger');
                return redirect()->route('unit.users', $unit->id);;
            }

            $request->session()->flash('message', 'Successfully added a user');
            $request->session()->flash('alert-class', 'alert-success');
            return redirect()->route('unit.users', $unit->id);
        }
    }


    /**
     * Delete user from the unit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  number $unit_id
     * @param  number $user_id
     * @param  number $role_id
     * @return \Illuminate\Http\Response
     */
     public function deleteUser(Request $request, $unit_id, $user_id, $role_id)
     {
       $unit = Unit::findOrFail($unit_id);
       $user = User::findOrFail($user_id);
       try {
            if($user->roles()->count() != 1){
                $user->roles()->where('id', $role_id)->wherePivot('unit_id', $unit_id)->detach($role_id);
                // check user's default unit
                if ($user->units()->where('is_default', 1)->count() == 0) {
                    // add default unit
                    $user->units()->updateExistingPivot($user->units()->first(), ['is_default' => '1']);
                }
            } else {
                $request->session()->flash('message', 'Can not delete. This User have only one role in the database');
                $request->session()->flash('alert-class', 'alert-danger');
                return redirect()->route('unit.users', $unit->id);
            }

       } catch (QueryException $e) {
           $request->session()->flash('message', 'Can not delete. Please check foreign data');
           $request->session()->flash('alert-class', 'alert-danger');
           return redirect()->route('unit..users', $unit->id);
       }
       $request->session()->flash('message', 'Successfully deleted');
       $request->session()->flash('alert-class', 'alert-success');
       return redirect()->route('unit.users', $unit->id);
     }
}
