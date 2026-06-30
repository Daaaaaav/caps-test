<?php

namespace App\Mail;

use App\Models\Guestbook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuestbookQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $scanUrl;
    public string $qrImageUrl;

    public function __construct(public Guestbook $entry)
    {
        $this->scanUrl   = route('guestbook.scan',     ['token' => $entry->qr_token]);

        // A public URL that serves the QR as SVG — no data: URI, no attachment,
        // no Imagick/GD needed. Works in all email clients including Gmail.
        $this->qrImageUrl = route('guestbook.qr.image', ['token' => $entry->qr_token]);
    }

    public function build(): static
    {
        return $this->subject('Konfirmasi Kunjungan – QR Code Tamu')
                    ->view('mail.guestbook-qr');
    }
}
