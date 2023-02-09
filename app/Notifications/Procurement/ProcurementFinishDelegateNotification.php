<?php

namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProcurementFinishDelegateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($procurement, $sender, $requester, $comment)
    {
        $this->formattedProcurementId = idFormatter('procurement', $procurement->id);

        $this->title = "Requisition notification(delegated has be returned) [{$this->formattedProcurementId}]";
        $this->procurement = $procurement;
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
        ->view('emails.procurement.finish_delegate_notification', [
            'procurement' => $this->procurement,
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
            'type' => 'procurement',
            'id' => $this->procurement->id,
            // 'unit_id' => $this->procurement->unit_id,
            // 'title' => $this->procurement->title,
            // 'data' => $this->procurement,
            'sender' => $this->sender,
            'receiver' => $this->requester,
            'comment' => $this->comment,
            'notification' => "The delegated procurement requisition ({$this->procurement->title}) has be returned",
        ];
    }
}
