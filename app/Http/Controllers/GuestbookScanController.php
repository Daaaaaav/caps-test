<?php

namespace App\Http\Controllers;

use App\Models\Guestbook;
use App\Models\GuestbookScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestbookScanController extends Controller
{
    /**
     * Show the QR scan landing page.
     * Accessible publicly (no auth required) — only a valid token is the gate.
     */
    public function show(string $token)
    {
        $entry = Guestbook::where('qr_token', $token)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $scans = $entry->scans()
            ->orderByDesc('scanned_at')
            ->limit(20)
            ->get();

        return view('guestbook.scan', compact('entry', 'scans'));
    }

    /**
     * Serve the QR code as a PNG image directly.
     * Used as the <img src> in the confirmation email — avoids data: URI blocking.
     */
    public function qrImage(string $token)
    {
        $entry = Guestbook::where('qr_token', $token)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $scanUrl = route('guestbook.scan', ['token' => $token]);

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $svg = (new \BaconQrCode\Writer($renderer))->writeString($scanUrl);

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Record one visitor scan.
     * Can be called repeatedly to accumulate group members.
     */
    public function submit(Request $request, string $token)
    {
        $entry = Guestbook::where('qr_token', $token)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Once jam_out is set the visit is over — no more scans allowed
        if ($entry->jam_out) {
            return redirect()
                ->route('guestbook.scan', ['token' => $token])
                ->with('error', 'Kunjungan ini sudah selesai.');
        }

        $data = $request->validate([
            'visitor_name'      => ['required', 'string', 'max:255'],
            'visitor_id_number' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($entry, $data, $request) {
            // Record the individual scan
            GuestbookScan::create([
                'guestbook_id'      => $entry->guestbook_id,
                'visitor_name'      => $data['visitor_name'],
                'visitor_id_number' => $data['visitor_id_number'] ?? null,
                'scanned_by_ip'     => $request->ip(),
                'scanned_at'        => now(),
            ]);

            // Increment visitor count and move status to 'ongoing' on first scan
            $entry->increment('visitor_count');
            if ($entry->qr_status === 'pending') {
                $entry->update(['qr_status' => 'ongoing']);
            }
        });

        return redirect()
            ->route('guestbook.scan', ['token' => $token])
            ->with('scan_success', "Selamat datang, {$data['visitor_name']}!");
    }
}
