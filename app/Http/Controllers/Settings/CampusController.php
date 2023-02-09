<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use App\Campus;
use App\District;
use Illuminate\Database\QueryException;

class CampusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campuses = Campus::get();
        return view('settings.Campus.index', compact('campuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $district = District::get();
        return view('settings.campus.create', compact('district'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            $validate=$request->validate([
                'name'=>'required|regex:/^[\pL\s\-]+$/u|unique:campuses',
                ],
                [
                    'name.regex'=>'The field only accept letters',
              ]);
             Campus::create($request->all());
      
             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('campuses.index');
         }
      
         /**
          * Display the specified resource.
          *
          * @param  \App\VehicleType  $vehicleType
          * @return \Illuminate\Http\Response
          */
         public function show(Campus $campus)
         {
         }
      
         /**
          * Show the form for editing the specified resource.
          *
          * @param  \App\VehicleType  $vehicleType
          * @return \Illuminate\Http\Response
          */
         public function edit(Campus $campus)
         {
        return view('settings.campus.edit', compact('campus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Campus::findOrFail($id)->update($request->all());

        $request->session()->flash('message', 'Successfully updated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('campuses.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Campus $campus)
    {
        try {
            $campus->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('campuses.index');
        }

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('campuses.index');
    }
}
