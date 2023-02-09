<?php

namespace App\Notifications\Travel;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TravelCancelNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($travel, $sender, $receiver, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $travel->id);

        $this->title = "Requisition notification(Canceled) [{$this->formattedTravelId}]";
        $this->travel = $travel;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject($this->title)
        ->view('emails.travel.cancel_notification', [
            'travel' => $this->travel,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'submitType' => 'Cancel',
            'type' => 'travel',
            'id' => $this->travel->id,
            // 'unit_id' => $this->travel->unit_id,
            // 'title' => $this->travel->title,
            // 'data' => $this->travel,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
            'notification'=> "The travel requisition, {$this->travel->title}, has been canceled"
        ];
    }
}
