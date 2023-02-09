<?php
     namespace App\Http\Controllers\Settings;
      
     use App\Http\Controllers\Controller;
      
     use App\Company;
     use Illuminate\Http\Request;
     use App\Unit;
     use Illuminate\Database\QueryException;
      
     class CompanyController extends Controller
     {
         /**
          * Display a listing of the resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function index()
         {
           $companies = Company::get();
           return view('settings.company.index',compact('companies'));
         }
      
         /**
          * Show the form for creating a new resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function create()
         {
             return view('settings.company.create');
         }
      
         /**
          * Store a newly created resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function store(Request $request)
         {
             Company::create($request->all());
      
             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('companies.index');
         }
      
         /**
          * Display the specified resource.
          *
          * @param  \App\Company  $company
          * @return \Illuminate\Http\Response
          */
         public function show(Company $company)
         {
         }
      
         /**
          * Show the form for editing the specified resource.
          *
          * @param  \App\Company  $company
          * @return \Illuminate\Http\Response
          */
         public function edit(Company $company)
         {
             return view('settings.company.edit', compact('company')) ;
         }
      
         /**
          * Update the specified resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function update(Request $request, $id)
         {
             Company::findOrFail($id)->update($request->all());
      
             $request->session()->flash('message', 'Successfully updated');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('companies.index');
         }
      
         /**
          * Remove the specified resource from storage.
          *
          * @param  \App\Company  $company
          * @return \Illuminate\Http\Response
          */
         public function destroy(Request $request, Company $company)
         {
             try {
                 $company->delete();
             } catch (QueryException $e) {
                 $request->session()->flash('message', 'Can not delete. Please check foreign data');
                 $request->session()->flash('alert-class', 'alert-danger');
                 return redirect()->route('companies.index');
             }
      
             $request->session()->flash('message', 'Successfully deleted');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('companies.index');
         }
         
         public function getUnits(Request $request, $id)
         {
             $company = Company::findOrFail($id);
             $units = $company->units;
             return view('settings.company.units', compact('units','company')) ;
         }
     }
  