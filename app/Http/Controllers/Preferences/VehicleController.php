<?php

namespace App\Http\Controllers\Preferences;

use App\Campus;
use App\Http\Controllers\Controller;
use App\Make;
use App\Unit;
use App\Vehicle;
use App\vehicleType;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehicles = Vehicle::with('vehicleType')->get();
        $units = Unit::with('vehicle')->get();
        $campuses = Campus::with('vehicle')->get();
        return view('preferences.vehicle.index', compact('vehicles', 'units', 'campuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $vehicleTypes = VehicleType::pluck('name', 'id');
        $campuses = Campus::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $makes = Make::pluck('make', 'id');
        return view('preferences.vehicle.create',compact('vehicleTypes', 'units','campuses','makes'));
        //return view('settings.vehicle.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration_number'=>'required|unique:vehicles',
            'mileage'=>'required|numeric',
            'capacity'=>'required|numeric',
            'colour'=>'required|regex:/^[\pL\s\-]+$/u',
             ],
             [
                'mileage.numeric'=>'The field only accept numbers',
                'capacity.numeric'=>'The field only accept numbers',
                'colour.regex'=>'The field only accept letters',
          ]);
        Vehicle::create($request->all());
        $request->session()->flash('message', 'Successfully created');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vehicles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $vehicle=Vehicle::find($id);
        $vehicleType=VehicleType::find($id);
        $campus = Campus::with('vehicle')->get();

        return view('preferences.vehicle.show',compact('vehicle','campus','vehicleType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vehicle  $vehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehicle $vehicle)
    {
        //dd($vehicle);
        // $vehicle = vehicle::pluck('make', 'id');
        $vehicleTypes = VehicleType::pluck('name', 'id');
        $campuses = Campus::pluck('name', 'id');
        $units = Unit::pluck('name', 'id');
        $makes = Make::pluck('make', 'id');
        return view('preferences.vehicle.edit',compact('vehicle','vehicleTypes', 'units','campuses','makes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate=$request->validate([
            'registration_number' => [
                'required',
                Rule::unique('vehicles')->ignore($id),
            ],
            'mileage'=>'required',
            'colour'=>'required|regex:/^[\pL\s\-]+$/u',
            'capacity'=>'numeric',
          ]);

        Vehicle::findOrFail($id)->update($request->all());
        $request->session()->flash('message', 'Successfully updated');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vehicles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vehicle  $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Vehicle $vehicle)
    {
        try {
            $vehicle->delete();
        } catch (QueryException $e) {
            $request->session()->flash('message', 'Can not delete. Please check foreign data');
            $request->session()->flash('alert-class', 'alert-danger');
            return redirect()->route('vehicles.index');
        }

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->route('vehicles.index');
    }
}
