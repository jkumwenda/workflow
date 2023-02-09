<?php

namespace App\Notifications\Subsistence;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubsistenceApprovalNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($subsistence, $sender, $receiver, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $subsistence->travel->id);

        $this->title = "Requisition notification(Approved) [{$this->formattedTravelId}]";
        $this->subsistence = $subsistence;
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
        ->view('emails.subsistence.approval_notification', [
            'subsistence' => $this->subsistence,
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
            'type' => 'subsistence',
            'id' => $this->subsistence->id,
            // 'unit_id' => $this->subsistence->unit_id,
            // 'title' => $this->subsistence->title,
            // 'data' => $this->subsistence,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
            'notification'=> "The subsistence for, {$this->subsistence->travel->procurement->title}, has been approved"
        ];
    }
}
