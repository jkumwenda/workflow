<?php

namespace App\Notifications\Transport;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TransportSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $transport, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedTravelId = idFormatter('travel', $transport->travel->id);

        $this->title = "Requisition notification [{$this->formattedTravelId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->transport = $transport;
        $this->nextFlow = $nextFlow;
        $this->nextUser = $nextUser;
        $this->currentTrail = $currentTrail;
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
        ->view('emails.transport.send_next_notification', [
            'submitType' => $this->submitType,
            'transport' => $this->transport,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
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
            'submitType' => $this->submitType,
            'type' => 'transport',
            'id' => $this->transport->id,
            // 'unit_id' => $this->transport->unit_id,
            // 'title' => $this->transport->title,
            // 'data' => $this->transport,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a transport requisition (%s) to attend to from %s (%s)",
                $this->transport->travel->procurement->title,
                $this->transport->travel->procurement->createdUser->name,
                $this->transport->travel->procurement->unit->name)
        ];
    }
}
