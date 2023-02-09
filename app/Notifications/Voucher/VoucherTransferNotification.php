<?php

namespace App\Notifications\Voucher;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VoucherTransferNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($voucher, $sender, $receiver, $comment)
    {
        $this->formattedProcurementId = idFormatter('voucher', $voucher->id);

        $this->title = "Requisition notification(Transferred) [{$this->formattedProcurementId}]";
        $this->voucher = $voucher;
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
        ->view('emails.voucher.transfer_notification', [
            'voucher' => $this->voucher,
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
            'submitType' => 'Transferred',
            'type' => 'voucher',
            'id' => $this->voucher->id,
            // 'unit_id' => $this->voucher->purchase->procurement->unit_id,
            // 'title' => $this->voucher->purchase->procurement->title,
            // 'data' => $this->voucher,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' => $this->comment,
            'notification'=> "The delegated procurement requisition ({$this->voucher->purchase->procurement->title}) has be returned",
        ];
    }
}
