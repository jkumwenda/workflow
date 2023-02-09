<?php

namespace App\Http\Controllers\Preferences;

use App\Role;
use App\Priority;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class PriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $priorities = Priority::get();
        $modules = collect(config('const.MODULE.PROCUREMENT') + config('const.MODULE.TRAVEL'))->keys();
        return view('preferences.priority.index',compact('priorities', 'modules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('description', 'id');
        $users = User::all()->pluck('full_name', 'id');
        $modules = collect(config('const.MODULE.PROCUREMENT') + config('const.MODULE.TRAVEL'));
        return view('preferences.priority.create', compact('roles','modules','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Priority::create([
            'module_id' => ++$request->module_id,
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
        ]);
      
             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('priority.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $priority = Priority::find($id);
        return view('preferences.priority.show',compact('priority'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Priority $priority)
    {
        $roles = Role::pluck('description', 'id');
        $users = User::all()->pluck('full_name', 'id');
        $modules = collect(config('const.MODULE.PROCUREMENT') + config('const.MODULE.TRAVEL'))->keys();
        return view('preferences.priority.edit', compact('priority', 'modules','roles','users')) ;
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
        Priority::findOrFail($id)->update([
            'module_id' => ++$request->module_id,
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
        ]);
      
             $request->session()->flash('message', 'Successfully updated');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('priority.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Priority $priority)
    {
        try {
            $priority->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('priority.index');
        }
 
        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('priority.index');
    }
}
