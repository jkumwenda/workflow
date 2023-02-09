<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Facades\Requisition;

class NotificationController extends Controller
{
    /**
     * (Private function)
     * Add attributes
     */
    private function addAttributes($notifications) {
        foreach ($notifications as $i => $notification) {
            $type = Str::singular($notification->data['type']);
            $comment = null;
            if (!empty($notification->data['comment'])) {
                $comment = $notification->data['comment'];
            } else if (!empty($notification->data['currentTrail']) && !empty($notification->data['currentTrail']['comment'])) {
                $comment = $notification->data['currentTrail']['comment'];
            }
            $notifications[$i]->type = $type;
            $notifications[$i]->comment = $comment;
            $notifications[$i]->formattedId = idFormatter($type, $notification->data['id']);
            $notifications[$i]->url = route("{$notification->type}/show", [$notification->data['id'], 'read' => $notification->id]);
        }
        return $notifications;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = $this->addAttributes(Auth::user()->unreadNotifications()->paginate(25));
        return view('notification.index',compact('notifications'));
    }

    /**
     * For ajax
     *
     * @return \Illuminate\Http\Response
     */
    public function check()
    {
        //notifications
        $count = Auth::user()->unreadNotifications()->count();
        $notifications = $this->addAttributes(Auth::user()->unreadNotifications()->limit(5)->latest()->get());

        $latest = [];
        foreach ($notifications as $notification) {
            $latest[] = [
                'url' => $notification->url,
                'type' => $notification->type,
                'formattedId' => idFormatter($notification->type, $notification->data['id']),
                'notification' => $notification->data['notification'],
                'comment' => $notification->comment,
            ];
        }

        //to be confirmed
        $confirmedCount = array_sum(Requisition::getConfirmedCount());

        return [
            'notification' => [
                'count' => $count,
                'latest' => $latest
            ],
            'confirmed' => [
                'count' => $confirmedCount,
            ]
        ];
    }
}
