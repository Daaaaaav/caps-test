<?php

namespace App\Mail;

use App\Models\Guestbook;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuestbookQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $scanUrl;
    public string $qrTempPath;

    public function __construct(public Guestbook $entry)
    {
        $this->scanUrl = route('guestbook.scan', ['token' => $entry->qr_token]);

        // Generate PNG via GD (no Imagick needed)
        $png = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($this->scanUrl);

        // Write to a temp file — $message->embed() in the view needs a file path
        $this->qrTempPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        file_put_contents($this->qrTempPath, $png);
    }

    public function build(): static
    {
        $mail = $this->subject('Konfirmasi Kunjungan – QR Code Tamu')
                     ->view('mail.guestbook-qr');

        // Clean up temp file after build
        if (file_exists($this->qrTempPath)) {
            register_shutdown_function(function () {
                @unlink($this->qrTempPath);
            });
        }

        return $mail;
    }
}
