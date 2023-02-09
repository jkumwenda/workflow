<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    /**
     * Index
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('user_setting.index');
    }

    /**
     * Profile
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        return view('user_setting.profile');
    }

    /**
     * Signature
     *
     * @return \Illuminate\View\View
     */
    public function signature()
    {
        return view('user_setting.signature');
    }

    /**
     * Add / Change Signature
     *
     * @return \Illuminate\View\View
     */
    public function saveSignature(Request $request)
    {
        DB::beginTransaction();
        try {
            //add signature
            $user = Auth::user();
            $user->signature = $request->signature;
            $user->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        $request->session()->flash('message', 'Successfully saved');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Delete Signature
     *
     * @return \Illuminate\View\View
     */
    public function deleteSignature(Request $request)
    {
        DB::beginTransaction();
        try {
            //delete signature
            $user = Auth::user();
            $user->signature = null;
            $user->save();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();

        $request->session()->flash('message', 'Successfully deleted');
        $request->session()->flash('alert-class', 'alert-success');
        return redirect()->back();
    }
}
