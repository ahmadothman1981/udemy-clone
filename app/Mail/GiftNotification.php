<?php

namespace App\Mail;

use App\Models\GiftPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public GiftPurchase $gift,
        public string $buyerName
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve received a gift! 🎁',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.gift-notification',
            with: [
                'gift' => $this->gift,
                'buyerName' => $this->buyerName,
                'itemTitle' => $this->gift->course?->title ?? $this->gift->bundle?->title ?? 'Course',
                'redemptionCode' => $this->gift->redemption_code,
                'message' => $this->gift->message,
            ],
        );
    }
}
