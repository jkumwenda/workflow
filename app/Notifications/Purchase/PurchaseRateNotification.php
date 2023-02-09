<?php

namespace App\Notifications\Purchase;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseRateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($purchase, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedPurchaseId = idFormatter('purchase', $purchase->id);

        $this->title = "Requisition notification [{$this->formattedPurchaseId}]";
        $this->purchase = $purchase;
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
        ->view('emails.purchase.rate_notification', [
            'purchase' => $this->purchase,
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
            'submitType' => 'rate',
            'type' => 'purchase',
            'id' => $this->purchase->id,
            // 'unit_id' => $this->purchase->procurement->unit_id,
            // 'title' => $this->purchase->procurement->title,
            // 'data' => $this->purchase,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a purchase requisition (%s) to rate supplier - %s",
                $this->purchase->procurement->title,
                $this->purchase->supplier->name
            )
        ];
    }
}
