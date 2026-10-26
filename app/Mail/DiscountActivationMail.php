<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{name:string,code:string,description:?string,type:string,value:float,starts_at:?string,ends_at:?string,url:string} $offer
     */
    public function __construct(public array $offer)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Foodify offer: ' . $this->offer['name']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.discount-activation',
            with: ['offer' => $this->offer]
        );
    }
}
