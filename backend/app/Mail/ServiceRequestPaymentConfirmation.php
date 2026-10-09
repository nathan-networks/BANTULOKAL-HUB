<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceRequestPaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest $serviceRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Terverifikasi — Invoice ' .
                $this->serviceRequest->invoice_number .
                ' — BANTULOKAL HUB'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request-payment-confirmation'
        );
    }
}
