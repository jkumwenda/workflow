<?php

namespace App\Notifications\Travel;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TravelSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $travel, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedTravelId = idFormatter('travel', $travel->id);

        $this->title = "Requisition notification [{$this->formattedTravelId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->travel = $travel;
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
        ->view('emails.travel.send_next_notification', [
            'submitType' => $this->submitType,
            'travel' => $this->travel,
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
            'type' => 'travel',
            'id' => $this->travel->id,
            // 'unit_id' => $this->travel->unit_id,
            // 'title' => $this->travel->title,
            // 'data' => $this->travel,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a travel requisition (%s) to attend to from %s (%s)",
                $this->travel->procurement->title,
                $this->travel->procurement->createdUser->name,
                $this->travel->procurement->unit->name)
        ];
    }
}
