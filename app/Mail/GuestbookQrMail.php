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
    public string $qrSvgData; // raw SVG string passed to view for embedData()

    public function __construct(public Guestbook $entry)
    {
        $this->scanUrl = route('guestbook.scan', ['token' => $entry->qr_token]);

        // Generate SVG — pure PHP, no Imagick/GD required
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $this->qrSvgData = (new Writer($renderer))->writeString($this->scanUrl);
    }

    public function build(): static
    {
        return $this->subject('Konfirmasi Kunjungan – QR Code Tamu')
                    ->view('mail.guestbook-qr');
    }
}
