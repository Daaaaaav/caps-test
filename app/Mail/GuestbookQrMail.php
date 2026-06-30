<?php

namespace App\Mail;

use App\Models\Guestbook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class GuestbookQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $scanUrl;
    public string $qrTempPath;

    public function __construct(public Guestbook $entry)
    {
        $this->scanUrl = route('guestbook.scan', ['token' => $entry->qr_token]);

        // Use SVG backend — pure PHP, zero image extension dependencies (no GD, no Imagick).
        // We then embed it as a base64 data URI which works in all modern email clients
        // that support HTML (Gmail, Outlook web, Apple Mail, Yahoo Mail).
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($this->scanUrl);

        // Encode SVG as base64 — unlike inline <svg> tags, a base64 image/svg+xml src
        // is treated as a regular image by email clients and is not stripped.
        $this->qrTempPath = 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function build(): static
    {
        return $this->subject('Konfirmasi Kunjungan – QR Code Tamu')
                    ->view('mail.guestbook-qr');
    }
}
