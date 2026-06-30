<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Kunjungan</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .wrapper {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: #4A2F24;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #CDDEA7;
            font-size: 22px;
            margin: 0 0 6px 0;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .header p {
            color: rgba(205,222,167,0.75);
            font-size: 13px;
            margin: 0;
        }
        .body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }
        .intro {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            margin: 0 0 28px 0;
        }
        .detail-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .detail-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .detail-box td {
            padding: 5px 0;
            vertical-align: top;
        }
        .detail-box td:first-child {
            color: #6b7280;
            width: 38%;
            font-weight: 500;
        }
        .detail-box td:last-child {
            color: #111827;
            font-weight: 600;
        }
        .qr-section {
            text-align: center;
            margin-bottom: 28px;
        }
        .qr-section p {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 16px 0;
        }
        .qr-section img {
            width: 220px;
            height: 220px;
            border: 4px solid #4A2F24;
            border-radius: 12px;
            padding: 8px;
            background: #fff;
            display: block;
            margin: 0 auto;
        }
        .qr-section .scan-count-note {
            font-size: 12px;
            color: #4E653D;
            font-weight: 600;
            margin-top: 12px;
            background: #f0f5ea;
            border: 1px solid #c3d9a8;
            border-radius: 20px;
            display: inline-block;
            padding: 5px 16px;
        }
        .url-box {
            background: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 11px;
            color: #6b7280;
            word-break: break-all;
            margin-bottom: 28px;
            text-align: center;
        }
        .url-box a {
            color: #4A2F24;
            text-decoration: none;
            font-weight: 600;
        }
        .note {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
            border-top: 1px solid #f3f4f6;
            padding-top: 20px;
        }
        .footer {
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            padding: 20px 40px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="wrapper">
    {{-- Header --}}
    <div class="header">
        <h1>&#128210; Konfirmasi Kunjungan</h1>
        <p>QR Code Tamu – Buku Tamu Digital</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Halo, {{ $entry->name }}!</p>
        <p class="intro">
            Pendaftaran kunjungan Anda telah berhasil dicatat oleh resepsionis kami.
            Tunjukkan atau scan QR code di bawah ini saat memasuki gedung.
            Setiap anggota rombongan dapat men-scan QR code ini secara bergantian untuk mencatat kehadiran masing-masing.
        </p>

        {{-- Visit details --}}
        <div class="detail-box">
            <table>
                <tr>
                    <td>Tanggal</td>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td>Jam Masuk</td>
                    <td>{{ $entry->jam_in }}</td>
                </tr>
                @if($entry->instansi)
                <tr>
                    <td>Instansi</td>
                    <td>{{ $entry->instansi }}</td>
                </tr>
                @endif
                @if($entry->keperluan)
                <tr>
                    <td>Keperluan</td>
                    <td>{{ $entry->keperluan }}</td>
                </tr>
                @endif
                <tr>
                    <td>Petugas</td>
                    <td>{{ $entry->petugas_penjaga }}</td>
                </tr>
            </table>
        </div>

        {{-- QR Code --}}
        <div class="qr-section">
            <p>Scan QR code ini untuk mencatat kehadiran Anda:</p>
            <img src="{{ $message->embed($qrTempPath) }}" alt="QR Code Kunjungan">
            <br>
            <span class="scan-count-note">
                &#10003; Dapat di-scan lebih dari satu kali untuk rombongan
            </span>
        </div>

        {{-- Fallback URL --}}
        <div class="url-box">
            Jika QR code tidak terbaca, klik tautan ini:<br>
            <a href="{{ $scanUrl }}">{{ $scanUrl }}</a>
        </div>

        <p class="note">
            Email ini dikirim otomatis oleh sistem. Harap simpan QR code ini hingga kunjungan selesai.
            Jika Anda tidak merasa mendaftar kunjungan ini, abaikan email ini atau hubungi resepsionis kami.
        </p>
    </div>

    {{-- Footer --}}
    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Buku Tamu Digital
    </div>
</div>
</body>
</html>
