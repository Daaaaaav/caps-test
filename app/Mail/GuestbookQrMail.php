<?php

namespace App\Mail;

use App\Models\Guestbook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestbookQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $qrCodeBase64;
    public string $scanUrl;

    public function __construct(public Guestbook $entry)
    {
        $this->scanUrl = route('guestbook.scan', ['token' => $entry->qr_token]);

        // Generate a PNG QR code and encode it as base64 for inline embedding
        $png = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($this->scanUrl);

        $this->qrCodeBase64 = base64_encode($png);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Kunjungan – QR Code Tamu',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.guestbook-qr',
        );
    }
}
