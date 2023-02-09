<?php

namespace App\Notifications\Transport;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TransportApprovalNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($transport, $sender, $receiver, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $transport->travel->id);

        $this->title = "Requisition notification(Approved) [{$this->formattedTravelId}]";
        $this->transport = $transport;
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
        ->view('emails.transport.approval_notification', [
            'transport' => $this->transport,
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
            'submitType' => 'Approve',
            'type' => 'transport',
            'id' => $this->transport->id,
            // 'unit_id' => $this->transport->unit_id,
            // 'title' => $this->transport->title,
            // 'data' => $this->transport,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
            'notification'=> "The transport for , {$this->transport->travel->procurement->title}, has been approved"
        ];
    }
}
