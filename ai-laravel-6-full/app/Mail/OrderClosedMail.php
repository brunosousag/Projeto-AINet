<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OrderClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "FunShirt order #{$this->order->id} completed",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.closed',
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(Storage::disk('local')->path("pdf_receipts/{$this->order->receipt_url}"))
                ->as("FunShirt-receipt-{$this->order->id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
