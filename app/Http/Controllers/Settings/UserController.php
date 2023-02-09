<?php
     namespace App\Http\Controllers\Settings;

     use App\Http\Controllers\Controller;

    // use App\Imports\UsersImport;
     use App\User;
     use App\Unit;
     use App\Role;
     use App\Campus;
     use App\Grade;
     use Illuminate\Http\Request;
     use DB;
     use Illuminate\Database\QueryException;
     use App\Imports\UsersImport;
     use Maatwebsite\Excel\Facades\Excel;

     class UserController extends Controller
     {
         /**
          * Display a listing of the resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function index()
         {
           $users = User::get();
           $campuses = Campus::get();
           $grades=Grade::pluck('name','id');
           return view('settings.user.index',compact('users','grades','campuses'));
         }

         /**
          * Show the form for creating a new resource.
          *
          * @return \Illuminate\Http\Response
          */
         public function create()
         {
             $campuses = Campus::pluck('name','id');
             $roles = Role::pluck('description','id');
             $units = Unit::pluck('name','id');
             $grades=Grade::pluck('name','id');
             return view('settings.user.create',compact('roles','units','grades','campuses'));
         }

         /**
          * Store a newly created resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function store(Request $request)
         {
            DB::beginTransaction();
            try {
                $request->validate([
                    'salutation' => ['required', 'string', 'max:255'],
                    'username' => ['required', 'string', 'max:255'],
                    'firstname' => ['required', 'string', 'max:255'],
                    'surname' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                ]);

                //create user
               $user = User::create([
                    'salutation' => $request->salutation,
                    'username' => $request->username,
                    'first_name' => $request->first_name,
                    'surname' => $request->surname,
                    'email' => $request->email,
                    'campus_id' => $request->campus_id,
                    'grade_id'=>$request->grade_id,
                    'phone_number'=>$request->phone_number,
                    'active'=> '1'
                ]);
            // add the roles
            $count = 1;
            foreach ($request->role_ids as $role_id){
                if ($role_id != NULL && $count == 1){
                    $user->roles()->attach($role_id,['unit_id' =>$request->unit_id]);
                }
                else
                {
                    $user->roles()->attach($role_id,['unit_id' =>$request->unit_id,'is_default'=>'0']);
                }
                $count++;
            }

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            DB::commit();

             $request->session()->flash('message', 'Successfully created');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('users.index');
         }

         /**
          * Display the specified resource.
          *
          * @param  \App\User  $user
          * @return \Illuminate\Http\Response
          */
         public function show(User $user)
         {
            return view('settings.user.show', compact('user')) ;
         }

         /**
          * Show the form for editing the specified resource.
          *
          * @param  \App\User  $user
          * @return \Illuminate\Http\Response
          */
         public function edit(User $user)
         {
             $campuses = Campus::pluck('name','id');
             $roles = Role::pluck('short_name','id');
             $units = Unit::pluck('name','id');
             $grades=Grade::pluck('name','id');
             return view('settings.user.edit', compact('roles','units','user','grades','campuses'));
         }

         /**
          * Update the specified resource in storage.
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function update(Request $request, $id)
         {
             User::findOrFail($id)->update($request->all());
             $request->session()->flash('message', 'Successfully updated');
             $request->session()->flash('alert-class', 'alert-success');
             return redirect()->route('users.index');
         }

         /**
          * Remove the specified resource from storage.
          *
          * @param  \App\User  $user
          * @return \Illuminate\Http\Response
          */
         public function destroy(Request $request, User $user)
         {
             try {
                 $user->delete();
             } catch (QueryException $e) {
                 $request->session()->flash('message', 'Can not delete. Please check foreign data');
                 $request->session()->flash('alert-class', 'alert-danger');
                 return redirect()->route('users.index');
             }

                $request->session()->flash('message', 'Successfully deleted');
                $request->session()->flash('alert-class', 'alert-success');
                return redirect()->route('users.index');
         }


         /**
          * Activate user
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $id user_id
          * @return \Illuminate\Http\Response
          */
         public function activate(Request $request, $id)
         {
             User::findOrFail($id)->update(['active' => '1']);
             return redirect()->route('users.index');
         }


         /**
          * Deactivate user
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $id user_id
          * @return \Illuminate\Http\Response
          */
         public function deactivate(Request $request, $id)
         {
             User::findOrFail($id)->update(['active' => '0']);
             return redirect()->route('users.index');
         }


         /**
          * Show units which the user belongs
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $id user_id
          * @return \Illuminate\Http\Response
          */
         public function userUnit(Request $request, $id)
         {
             $user = User::findOrFail($id);
             //dd($user->roles);

            return view('settings.user.show', compact('user')) ;
         }


         /**
          * Show Adding role page
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $id user_id
          * @return \Illuminate\Http\Response
          */
         public function addRole(Request $request, $id)
         {
            $user = User::findOrFail($id);
            $roles = Role::pluck('description','id');
            $units = Unit::pluck('name','id');
            return view('settings.user.addrole', compact('roles','units','user')) ;
         }


         /**
          * Attach role for the unit to the user
          *
          * @param  \Illuminate\Http\Request  $request
          * @return \Illuminate\Http\Response
          */
         public function attachRole(Request $request)
         {
            $user = User::findOrFail($request->user_id);

            $role_exists = DB::table('role_user')->where([
                ['unit_id', '=', $request->unit_id],
                ['role_id', '=', $request->role_id],
            ])->exists();

            $role = Role::findOrFail($request->role_id);
            if($role->single_user == '1' && $role_exists){
                $request->session()->flash('message', 'Can not attach a role.  There already exists a person with that role in the unit');
                $request->session()->flash('alert-class', 'alert-danger');
                // return redirect()->route('user.unitRoles',$request->user_id);
                return redirect()->back()->withInput($request->input());
            } else {

                try {

                    $user->roles()->attach($request->role_id,['unit_id' =>$request->unit_id,'is_default'=>'0']);
                } catch (QueryException $e) {
                    $request->session()->flash('message', 'Can not attach a role. Please check foreign data or if a role is a single user');
                    $request->session()->flash('alert-class', 'alert-danger');
                    return redirect()->route('user.unitRoles', $user->id);;
                }

                $request->session()->flash('message', 'Successfully added a role');
                $request->session()->flash('alert-class', 'alert-success');
                return redirect()->route('user.unitRoles', $user->id);
            }
         }


         /**
          * Delete role page
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  number $user_id
          * @param  number $unit_id
          * @param  number $role_id
          * @return \Illuminate\Http\Response
          */
          public function deleteRole(Request $request,$user_id, $unit_id, $role_id)
          {
            $user = User::findOrFail($user_id);
            try {
                if($user->roles()->count() != 1){
                    // $user->roles()->detach($role_id,['unit_id' =>$unit_id]); DOESN'T WORK!
                    $user->roles()->where('id', $role_id)->wherePivot('unit_id', $unit_id)->detach($role_id);

                    // check user's default unit
                    if ($user->units()->where('is_default', 1)->count() == 0) {
                        // add default unit
                        $user->units()->updateExistingPivot($user->units()->first(), ['is_default' => '1']);
                    }
                }
                else
                {
                    $request->session()->flash('message', 'Can not delete. This User have only one role in the database');
                    $request->session()->flash('alert-class', 'alert-danger');
                    return redirect()->route('user.unitRoles', $user->id);
                }

            } catch (QueryException $e) {
                $request->session()->flash('message', 'Can not delete. Please check foreign data');
                $request->session()->flash('alert-class', 'alert-danger');
                return redirect()->route('user.unitRoles', $user->id);
            }
            $request->session()->flash('message', 'Successfully deleted');
            $request->session()->flash('alert-class', 'alert-success');
            return redirect()->route('user.unitRoles', $user->id);
          }


          /**
           * Change default unit
           *
           * @param  \Illuminate\Http\Request  $request
           * @param  number $id user_id
           * @return \Illuminate\Http\Response
           */
          public function changeDefault(Request $request, $user_id, $unit_id)
          {
              DB::beginTransaction();
              try {
                DB::table('role_user')->where([
                    ['user_id', '=', $user_id],
                    ['is_default', '=', '1'],
                ])->update(['is_default' => '0']);

                DB::table('role_user')->where([
                    ['user_id', '=', $user_id],
                    ['unit_id', '=',$unit_id],
                ])->update(['is_default' => '1']);
                DB::commit();

                $request->session()->flash('message', 'Successfully changed the default unit');
                $request->session()->flash('alert-class', 'alert-success');
                return redirect()->route('user.unitRoles', $user_id);

              } catch (QueryException $e) {
                DB::rollback();
                $request->session()->flash('message', 'Could not update the default unit');
                $request->session()->flash('alert-class', 'alert-danger');
                return redirect()->route('user.unitRoles', $user_id);
              }
          }

         public function importUsers(){
             return view('settings.user.import') ;
         }

         public function import()
         {
             Excel::import(new UsersImport, request()->file('file')->store('temp'));

             request()->session()->flash('message', 'Users successfully created');
             request()->session()->flash('alert-class', 'alert-success');
             return redirect()->route('users.index');
         }
     }
