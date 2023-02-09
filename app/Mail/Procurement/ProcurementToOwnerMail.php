<?php

namespace App\Mail\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcurementToOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($submitType, $procurement, $nextFlow, $nextUser, $currentTrail, $comment)
    {
        $this->formattedProcurementId = idFormatter('procurement', $procurement->id);
        $this->title = "Requisition notification [{$this->formattedProcurementId}]";
        $this->submitType = $submitType;
        $this->procurement = $procurement;
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
            ->view('emails.procurement.to_owner_notification')
            ->text('emails.procurement.to_owner_notification_text')
            ->subject($this->title)
            ->with([
                'submitType' => $this->submitType,
                'procurement' => $this->procurement,
                'nextFlow' => $this->nextFlow,
                'nextUser' => $this->nextUser,
                'currentTrail' => $this->currentTrail,
                'comment' => $this->comment
            ]);
    }
}
