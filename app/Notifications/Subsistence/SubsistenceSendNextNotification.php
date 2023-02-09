<?php

namespace App\Notifications\Subsistence;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubsistenceSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $subsistence, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedTravelId = idFormatter('travel', $subsistence->travel->id);

        $this->title = "Requisition notification [{$this->formattedTravelId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->subsistence = $subsistence;
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
        ->view('emails.subsistence.send_next_notification', [
            'submitType' => $this->submitType,
            'subsistence' => $this->subsistence,
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
            'type' => 'subsistence',
            'id' => $this->subsistence->id,
            // 'unit_id' => $this->subsistence->unit_id,
            // 'title' => $this->subsistence->title,
            // 'data' => $this->subsistence,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a subsistence requisition (%s) to attend to from %s (%s)",
                $this->subsistence->travel->procurement->title,
                $this->subsistence->travel->procurement->createdUser->name,
                $this->subsistence->travel->procurement->unit->name)
        ];
    }
}
