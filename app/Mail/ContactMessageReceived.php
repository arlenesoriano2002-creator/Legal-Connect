<?php

namespace App\Mail;

use App\Models\ConcernsInquiriesMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(ConcernsInquiriesMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Contact Message Received - Legal Connect')
                    ->view('emails.contact-message')
                    ->with([
                        'contactMessage' => $this->message
                    ]);
    }
}