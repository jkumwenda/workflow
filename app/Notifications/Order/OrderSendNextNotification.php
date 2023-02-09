<?php

namespace App\Notifications\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $order, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedOrderId = idFormatter('order', $order->id);

        $this->title = "Requisition notification (LPO verification and approval) [{$this->formattedOrderId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->order = $order;
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
        ->view('emails.order.send_next_notification', [
            'submitType' => $this->submitType,
            'order' => $this->order,
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
            'type' => 'order',
            'id' => $this->order->id,
            // 'unit_id' => $this->order->purchase->procurement->unit_id,
            // 'title' => $this->order->purchase->procurement->title,
            // 'data' => $this->order,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification'=> sprintf("You have a order requisition (%s [%s]) to attend to from (%s)",
                $this->order->purchase->procurement->title,
                $this->order->purchase->supplier->name,
                $this->order->purchase->procurement->unit->name),

        ];
    }
}
