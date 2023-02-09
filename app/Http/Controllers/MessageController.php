<?php

namespace App\Http\Controllers;

use \Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Http\Requests\MessageSendRequest;
use App\Http\Requests\MessageAnswerRequest;

use App\Message;
use App\User;


use App\Notifications\MessageNotification;

class MessageController extends Controller
{
    /**
     * Send message
     *
     * @return \App\Http\Requests\MessageSendRequest
     */
    public function send(MessageSendRequest $request)
    {

        DB::beginTransaction();
        try {
            // insert message table
            $newMessage = new Message([
                'messageable_type' => $request->messageable_type,
                'messageable_id' => $request->messageable_id,
                'questioner_user_id' => Auth::user()->id,
                'receiver_user_id' => $request->receiver,
                'question' => $request->question,
            ]);
            $newMessage->save();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        $messageable = DB::table($request->messageable_type)->find($request->messageable_id);

        //notification & sending mail
        //to receiver
        Notification::send($newMessage->receiver, new MessageNotification($newMessage, $messageable));

        DB::commit();

        return redirect()->back();
    }

    /**
     * Answer message
     *
     * @return App\Http\Requests\MessageAnswerRequest
     */
    public function answer(MessageAnswerRequest $request)
    {

        $messageId = $request->message_id;
        $message = Message::findOrFail($messageId);

        DB::beginTransaction();
        try {
            $message->answer = $request->answer;
            $message->answerer_user_id = Auth::user()->id;
            $message->save();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        $messageable = DB::table($message->messageable_type)->find($message->messageable_id);

        //notification & sending mail
        //to sender
        Notification::send($message->receiver, new MessageNotification($message, $messageable));

        DB::commit();

        return redirect()->back();
    }

}
