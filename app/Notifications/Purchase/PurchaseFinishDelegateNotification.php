<?php

namespace App\Notifications\Purchase;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseFinishDelegateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($purchase, $sender, $requester, $comment)
    {
        $this->formattedPurchaseId = idFormatter('purchase', $purchase->id);

        $this->title = "Requisition notification(delegated has been returned) [{$this->formattedPurchaseId}]";
        $this->purchase = $purchase;
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
        ->view('emails.purchase.finish_delegate_notification', [
            'purchase' => $this->purchase,
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
            'type' => 'purchase',
            'id' => $this->purchase->id,
            // 'unit_id' => $this->purchase->unit_id,
            // 'title' => $this->purchase->title,
            // 'data' => $this->purchase,
            'sender' => $this->sender,
            'receiver' => $this->requester,
            'comment' => $this->comment,
            'notification' => "The delegated purchase requisition ({$this->purchase->title}) has been returned",
        ];
    }
}
