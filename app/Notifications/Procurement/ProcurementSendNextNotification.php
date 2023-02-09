<?php

namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProcurementSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $procurement, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedProcurementId = idFormatter('procurement', $procurement->id);

        $this->title = "Requisition notification [{$this->formattedProcurementId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->procurement = $procurement;
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
        ->view('emails.procurement.send_next_notification', [
            'submitType' => $this->submitType,
            'procurement' => $this->procurement,
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
            'type' => 'procurement',
            'id' => $this->procurement->id,
            // 'unit_id' => $this->procurement->unit_id,
            // 'title' => $this->procurement->title,
            // 'data' => $this->procurement,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a procurement requisition (%s) to attend to from %s (%s)",
                $this->procurement->title,
                $this->procurement->createdUser->name,
                $this->procurement->unit->name),
        ];
    }
}
