<?php

namespace App\Notifications\Voucher;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VoucherSendNextNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($submitType, $voucher, $nextFlow, $nextUser, $currentTrail)
    {
        $this->formattedVoucherId = idFormatter('voucher', $voucher->id);

        $this->title = "Requisition notification [{$this->formattedVoucherId}]";
        $this->submitType = $submitType == 'submit' ? 'submit' : 'return';
        $this->voucher = $voucher;
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
        ->view('emails.voucher.send_next_notification', [
            'submitType' => $this->submitType,
            'voucher' => $this->voucher,
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
            'type' => 'voucher',
            'id' => $this->voucher->id,
            // 'unit_id' => $this->voucher->purchase->procurement->unit_id,
            // 'title' => $this->voucher->purchase->procurement->title,
            // 'data' => $this->voucher,
            'nextFlow' => $this->nextFlow,
            'nextUser' => $this->nextUser,
            'currentTrail' => $this->currentTrail,
            'notification' => sprintf("You have a voucher requisition (%s [%s]) to attend to from %s",
                $this->voucher->purchase->procurement->title,
                $this->voucher->purchase->supplier->name,
                $this->voucher->purchase->procurement->unit->name),
        ];
    }
}
