<?php
     namespace App\Http\Controllers\Settings;

     use App\Http\Controllers\Controller;

     use App\Role;
     use Illuminate\Http\Request;

     use Illuminate\Database\QueryException;

     class RoleController extends Controller
     {
         /**
          * Display a listing of the resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function index()
         {
           $roles = Role::get();
           return view('settings.role.index',compact('roles'));
         }

         /**
          * Show the form for creating a new resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function create()
         {
             return view('settings.role.create');
         }

         /**
          * Store a newly created resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function store(Request $request)
         {
             Role::create($request->all());

             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
              return redirect()->route('roles.index');
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
             return view('settings.role.edit', compact('role')) ;
         }

         /**
          * Update the specified resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function update(Request $request, $id)
         {
             Role::findOrFail($id)->update($request->all());

             $request->session()->flash('message', 'Successfully updated');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('roles.index');
         }

         /**
          * Remove the specified resource from storage.
          *
          * @param  \App\Role  $role
          * @return \Illuminate\Http\Response
          */
         public function destroy(Request $request, Role $role)
         {
             try {
                 $role->delete();
             } catch (QueryException $e) {
                 $request->session()->flash('message', 'Can not delete. Please check foreign data');
                 $request->session()->flash('alert-class', 'alert-danger');
                 return redirect()->route('roles.index');
             }

             $request->session()->flash('message', 'Successfully deleted');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('roles.index');
         }


         /**
          * Show users have the specific role
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $roleId
          * @return \Illuminate\Http\Response
          */
         public function users(Request $request, $id)
         {
             $role = Role::findOrFail($id);

            return view('settings.role.users', compact('role')) ;
         }
     }
