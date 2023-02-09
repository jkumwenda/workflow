<?php

namespace App\Mail\Travel;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TravelToOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($submitType, $travel, $nextFlow, $nextUser, $currentTrail, $comment)
    {
        $this->formattedTravelId = idFormatter('travel', $travel->id);
        $this->title = "Requisition notification [{$this->formattedTravelId}]";
        $this->submitType = $submitType;
        $this->travel = $travel;
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
            ->view('emails.travel.to_owner_notification')
            ->text('emails.travel.to_owner_notification_text')
            ->subject($this->title)
            ->with([
                'submitType' => $this->submitType,
                'travel' => $this->travel,
                'nextFlow' => $this->nextFlow,
                'nextUser' => $this->nextUser,
                'currentTrail' => $this->currentTrail,
                'comment' => $this->comment
            ]);
    }
}
