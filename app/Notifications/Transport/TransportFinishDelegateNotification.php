<?php

namespace App\Notifications\Transport;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TransportFinishDelegateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($transport, $sender, $requester, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $transport->travel->id);

        $this->title = "Requisition notification(delegated has be returned) [{$this->formattedTravelId}]";
        $this->transport = $transport;
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
        ->view('emails.transport.finish_delegate_notification', [
            'transport' => $this->transport,
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
            'type' => 'transport',
            'id' => $this->transport->id,
            // 'unit_id' => $this->transport->unit_id,
            // 'title' => $this->transport->title,
            // 'data' => $this->transport,
            'sender' => $this->sender,
            'receiver' => $this->requester,
            'comment' => $this->comment,
            'notification' => "The delegated transport requisition ({$this->transport->travel->procurement->title}) has be returned",
        ];
    }
}
