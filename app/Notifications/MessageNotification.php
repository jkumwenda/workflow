<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $messageable)
    {
        $this->formattedId = idFormatter($message->messageable_type, $message->messageable_id);

        $this->title = "R-PLUS message received [{$this->formattedId}]";
        $this->message = $message;
        $this->messageable = $messageable;

        $this->sender   = empty($this->message->answer) ? $this->message->questioner : $this->message->answerer;
        $this->receiver = empty($this->message->answer) ? $this->message->receiver : $this->message->questioner;
        $this->comment  = empty($this->message->answer) ? $this->message->question : $this->message->answer;


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
        ->view('emails.message', [
            'messageableType' => $this->message->messageable_type,
            'messageableId' => $this->message->messageable_id,
            'messageable' => $this->messageable,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'questioner' => $this->message->questioner,
            'answerer' => $this->message->answerer,
            'question' => $this->message->question,
            'answer' => $this->message->answer,
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
            'submitType' => 'sendMessage',
            'type' => $this->message->messageable_type,
            'id' => $this->message->messageable_id,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'comment' =>  $this->comment,
            'notification'=> "You got a message from {$this->sender->name}.",
        ];
    }
}
