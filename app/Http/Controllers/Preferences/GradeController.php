<?php

namespace App\Http\Controllers\Preferences;

use App\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grades = Grade::get();
        return view('preferences.grade.index',compact('grades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('preferences.grade.create');
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
            'name' => 'required|unique:grades',
            'subsistence' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'lunch' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'international' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        ],
        [
            'subsistence.regex' => 'Subsistence amount exceeds the limit of 2 decimals',
            'lunch.regex' => 'Lunch amount exceeds the limit of 2 decimals',
            'international.regex' => 'International amount exceeds the limit of 2 decimals'
        ]);

        Grade::create($request->all());
      
             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('grades.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $grade = Grade::find($id);
        return view('preferences.grade.show',compact('grade'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Grade $grade)
    {
        return view('preferences.grade.edit', compact('grade')) ;
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
        $request->validate([
            'name' => [
                'required',
                Rule::unique('grades')->ignore($id),
            ],
            'subsistence' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'lunch' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'international' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        ],
        [
            'subsistence.regex' => 'Subsistence amount exceeds the limit of 2 decimals',
            'lunch.regex' => 'Lunch amount exceeds the limit of 2 decimals',
            'international.regex' => 'International amount exceeds the limit of 2 decimals'
        ]);

        
        Grade::findOrFail($id)->update($request->all());
      
             $request->session()->flash('message', 'Successfully updated');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('grades.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Grade $grade)
         {
             try {
                 $grade->delete();
             } catch (QueryException $e) {
                 $request->session()->flash('message', 'Can not delete. Please check foreign data');
                 $request->session()->flash('alert-class', 'alert-danger');
                 return redirect()->route('grades.index');
             }
      
             $request->session()->flash('message', 'Successfully deleted');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('grades.index');
         }
}
