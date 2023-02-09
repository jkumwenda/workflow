<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Facades\Requisition;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //counts
        $confirmed = array_sum(Requisition::getConfirmedCount());
        $inProgress = Requisition::getInProgressCount();
        $completed = Requisition::getCompletedCount();
        $delegated = Requisition::getDelegatedCount();
        $notifications = Auth::user()->unreadNotifications()->count();

        $counts = compact('confirmed', 'inProgress', 'completed', 'delegated', 'notifications');

        return view('dashboard', compact('counts'));
    }

    public function notYet() {
        return '404 : not generated';
    }
}
