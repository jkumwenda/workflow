<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;

use App\VehicleType;
use Illuminate\Http\Request;
use App\Vehicle;
use Illuminate\Database\QueryException;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicleTypes = VehicleType::get();
        return view('settings.vehicleType.index', compact('vehicleTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('settings.vehicleType.create');
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
                'name'=>'required|unique:vehicle_types|regex:/^[\pL\s\-]+$/u',

              ],
              [

                'name.regex'=>'The field only accept letters',
              ]);

             VehicleType::create($request->all());
      
             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('vehicleTypes.index');
         }
      
         /**
          * Display the specified resource.
          *
          * @param  \App\VehicleType  $vehicleType
          * @return \Illuminate\Http\Response
          */
         public function show($id)
         {
           $vehicleShows=VehicleType::find($id);
           $vehicleTypes=VehicleType::find($id);
           
            return view('settings.vehicleType.show', compact('vehicleShows','vehicleTypes'));
         }
      
         /**
          * Show the form for editing the specified resource.
          *
          * @param  \App\VehicleType  $vehicleType
          * @return \Illuminate\Http\Response
          */
         public function edit(VehicleType $vehicleType)
         {
        return view('settings.vehicleType.edit', compact('vehicleType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        VehicleType::findOrFail($id)->update($request->all());

        $request->session()->flash('message', 'Successfully updated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vehicleTypes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\VehicleType  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, VehicleType $vehicleType)
    {
        try {
            $vehicleType->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('vehicleTypes.index');
        }

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vehicleTypes.index');
    }

    public function getVehicles(Request $request, $id)
    {
        $vehicleType = VehicleType::findOrFail($id);
        $vehicles = $vehicleType->vehicles;
        return view('settings.vehicleType.vehicles', compact('vehicles', 'vehicleType'));
    }
}
