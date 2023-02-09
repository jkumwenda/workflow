<?php

namespace App\Notifications\Purchase;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseDelegateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($purchase, $sender, $receiver, $comment)
    {
        $this->formattedPurchaseId = idFormatter('purchase', $purchase->id);

        $this->title = "Requisition notification(Delegation) [{$this->formattedPurchaseId}]";
        $this->purchase = $purchase;
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
        ->view('emails.purchase.delegate_notification', [
            'purchase' => $this->purchase,
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
            'submitType' => 'Delegate',
            'type' => 'purchase',
            'id' => $this->purchase->id,
            // 'unit_id' => $this->purchase->procurement->unit_id,
            // 'title' => $this->purchase->procurement->title,
            // 'data' => $this->purchase,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
            'notification' => sprintf("You have a delegated purchase requisition (%s [%s]) to attend to from %s",
                $this->purchase->procurement->title,
                $this->purchase->supplier->name,
                $this->sender->name),
        ];
    }
}
