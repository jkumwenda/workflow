<?php

namespace App\Imports;

use App\Campus;
use App\Grade;
use App\Role;
use App\Unit;
use App\User;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    private $campus;
    private $unit;
    private $grade;
    private $role;
    private $user;

    public function __construct()
    {
        $this-> campus = Campus::select('id', 'name')->get();
        $this-> grade = Grade::select('id', 'name')->get();
        $this-> role = Role::select('id', 'description')->get();
        $this-> unit = Unit::select('id', 'name')->get();
        $this-> user = User::select('id', 'email')->get();
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $campus = $this->campus->where('name', $row['campus'])->first();
        $grade = $this->grade->where('name', $row['grade'])->first();
        $user = $this->user->where('email', $row['email'])->first();

        if($user){
            $user->salutation = $row['salutation'];
            $user->username = $row['username'];
            $user->first_name = $row['first_name'];
            $user->surname = $row['surname'];
            $user->phone_number = $row['phone_number'];
            $user->campus_id = $campus->id ?? NULL;
            $user->grade_id = $grade->id ?? NULL;
            $user->save();
        } else{
            $user = new User([
                'salutation' => $row['salutation'],
                'username' => $row['username'],
                'first_name' => $row['first_name'],
                'surname' => $row['surname'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'campus_id' => $campus->id ?? NULL,
                'grade_id' => $grade->id ?? NULL
            ]);
        }

        $roleNames = explode(',', $row['role']);
        $roles = $this->role->whereIn('description', $roleNames)->first();

        $unitNames = explode(',', $row['unit']);
        $unit = $this->unit->whereIn('name', $unitNames)->first();

        $user->roles()->attach($roles, ['unit_id'=> $unit->id]);


//        $count = 1;
//        foreach ($roles as $role_id){
//            if ($role_id != NULL && $count == 1){
//                $user->roles()->attach($roles, ['unit_id'=> $unit->id]);
//            }
//            else
//            {
//                $user->roles()->attach($role_id,['unit_id' =>$unit->id,'is_default'=>'0']);
//            }
//            $count++;
//        }

        return $user;
    }

    public function rules(): array
    {
        return [
            'email' => Rule::unique('users', 'email')
        ];
    }
}
