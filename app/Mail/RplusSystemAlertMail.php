<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RplusSystemAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($messages, $content, $url)
    {
        $this->title = 'RPLUS System Alert';
        $this->messages = $messages;
        $this->content = $content;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.system_alert')
            ->subject($this->title)
            ->with([
                'messages' => $this->messages,
                'content' => $this->content,
                'url' => $this->url,
            ]);
    }
}
