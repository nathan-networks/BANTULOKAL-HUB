<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentalBookingPaymentInstructions extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Instruksi Pembayaran Sewa #' . $this->booking->id .
                ' - BANTULOKAL HUB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rental-booking-payment-instructions',
        );
    }
}
