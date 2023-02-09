<?php

namespace App\Notifications\Travel;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TravelFinishDelegateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($travel, $sender, $requester, $comment)
    {
        $this->formattedProcurementId = idFormatter('travel', $travel->id);

        $this->title = "Requisition notification(delegated has be returned) [{$this->formattedProcurementId}]";
        $this->travel = $travel;
        $this->sender = $sender;
        $this->requester = $requester;
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
        ->view('emails.travel.finish_delegate_notification', [
            'travel' => $this->travel,
            'sender' => $this->sender,
            'requester' => $this->requester,
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
            'submitType' => 'FinishDelegate',
            'type' => 'travel',
            'id' => $this->travel->id,
            // 'unit_id' => $this->travel->unit_id,
            // 'title' => $this->travel->title,
            // 'data' => $this->travel,
            'sender' => $this->sender,
            'receiver' => $this->requester,
            'comment' => $this->comment,
            'notification' => "The delegated travel requisition ({$this->travel->title}) has be returned",
        ];
    }
}
