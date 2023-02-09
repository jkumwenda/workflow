<?php

namespace App\Mail\Transport;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TransportToOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($submitType, $transport, $nextFlow, $nextUser, $currentTrail, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $transport->travel->id);
        $this->title = "Requisition notification [{$this->formattedTravelId}]";
        $this->submitType = $submitType;
        $this->transport = $transport;
        $this->nextFlow = $nextFlow;
        $this->nextUser = $nextUser;
        $this->currentTrail = $currentTrail;
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.transport.to_owner_notification')
            ->text('emails.transport.to_owner_notification_text')
            ->subject($this->title)
            ->with([
                'submitType' => $this->submitType,
                'transport' => $this->transport,
                'nextFlow' => $this->nextFlow,
                'nextUser' => $this->nextUser,
                'currentTrail' => $this->currentTrail,
                'comment' => $this->comment
            ]);
    }
}
