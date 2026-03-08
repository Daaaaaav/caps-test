<?php

namespace App\Http\Controllers;

// Import Controller dasar (BaseController)
use Illuminate\Routing\Controller as BaseController; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Pastikan extends ke BaseController atau Controller (jika Controller.php ada)
// Kita gunakan file Controller.php yang barusan kita buat
class AttachmentController extends Controller
{
    /**
     * Upload file sementara ke local public disk.
     * Menggunakan Storage::disk('public') secara eksplisit agar pasti benar.
     */
    public function tempUpload(Request $req)
    {
        // Log untuk memastikan fungsi ini terpanggil
        Log::info('AttachmentController::tempUpload Dijalankan.');

        $req->validate([
            'file'    => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xlsx,zip'],
            'tmp_key' => ['required', 'string', 'max:120'],
        ]);

        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('tempUpload failed: User not authenticated.');
                return response()->json(['error' => 'User not authenticated.'], 401);
            }

            // Path ini relatif terhadap root disk 'public' (storage/app/public)
            // INI SUDAH SESUAI PERMINTAANMU: "ticket/tmp/..."
            $tmpFolder = "ticket/tmp/{$user->id}/" . $req->tmp_key;

            $file = $req->file('file');
            $filename = Str::random(10) . '_' . $file->getClientOriginalName();
            
            // Simpan ke disk 'public' secara eksplisit
            Log::info("Menyimpan file ke: {$tmpFolder}/{$filename} (disk public)");
            $path = $file->storeAs($tmpFolder, $filename, 'public');

            // Generate URL publik dari disk 'public'
            $url = Storage::url($path);
            Log::info("File berhasil disimpan, URL: {$url}");

            return response()->json([
                'public_id' => $path, 
                'secure_url' => $url,
                'bytes' => $file->getSize(),
                'resource_type' => 'raw',
                'format' => $file->getClientOriginalExtension(),
                'original_filename' => $file->getClientOriginalName(),
            ]);

        } catch (\Exception $e) {
            // Catat error 500
            Log::error('tempUpload CRASHED', [
                'user_id' => Auth::id() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Kembalikan response JSON error
            return response()->json(['error' => 'Upload failed on server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus file sementara dari disk 'public'.
     */
    public function deleteTemp(Request $req)
    {
        Log::info('AttachmentController::deleteTemp Dijalankan.');
        $req->validate([
            'public_id' => ['required', 'string'], // Ini berisi path file relatif di disk 'public'
        ]);

        try {
            // Hapus dari disk 'public' secara eksplisit
            if (Storage::disk('public')->exists($req->public_id)) {
                Storage::disk('public')->delete($req->public_id);
                Log::info("File temp dihapus: {$req->public_id}");
            } else {
                Log::warning("deleteTemp: File tidak ditemukan untuk dihapus: {$req->public_id}");
            }
            return response()->json(['ok' => true]);

        } catch (\Exception $e) {
            Log::warning('deleteTemp failed', [
                'user_id' => Auth::id(),
                'path' => $req->public_id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Delete failed on server'], 500);
        }
    }

    /**
     * Finalize: pindahkan dari tmp ke folder final tiket di disk 'public'.
     */
    public function finalizeTemp(Request $req)
    {
        Log::info('AttachmentController::finalizeTemp Dijalankan.');
        $req->validate([
            'ticket_id' => ['required', 'integer'], // Validasi exists bisa dilakukan di CreateTicket.php
            'items' => ['required', 'array'],
            'items.*.public_id' => ['required', 'string'], // Path source relatif di disk 'public'
            'items.*.bytes' => ['required', 'integer'],
            'items.*.original_filename' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $ticketId = $req->ticket_id;
        
        // Folder tujuan: INI JUGA SUDAH SESUAI PERMINTAANMU: "ticket/{$ticketId}"
        $finalFolder = "ticket/{$ticketId}";
        Log::info("Finalisasi attachment untuk Ticket ID: {$ticketId} ke folder: {$finalFolder}");

        foreach ($req->items as $item) {
            try {
                $srcPath = $item['public_id']; // contoh: ticket/tmp/1/uuid/file.jpg
                
                if (!Storage::disk('public')->exists($srcPath)) {
                    Log::warning('Temp file not found during finalize', ['src' => $srcPath, 'ticket_id' => $ticketId]);
                    continue;
                }

                $filename = basename($srcPath);
                $destPath = "{$finalFolder}/{$filename}"; // contoh: ticket/101/file.jpg

                // Pindahkan file di dalam disk 'public'
                Storage::disk('public')->move($srcPath, $destPath);
                Log::info("File dipindahkan: {$srcPath} -> {$destPath}");
                
                $finalUrl = Storage::url($destPath);

                // Insert ke database
                DB::table('ticket_attachments')->insert([
                    'ticket_id' => $ticketId,
                    'file_url' => $finalUrl,
                    'cloudinary_public_id' => null, // Pastikan ini null
                    'file_type' => $item['format'] ?? pathinfo($filename, PATHINFO_EXTENSION),
                    'uploaded_by' => $user->id,
                    'bytes' => $item['bytes'],
                    'original_filename' => $item['original_filename'] ?? $filename,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('finalizeTemp item failed', [
                    'ticket_id' => $ticketId,
                    'item' => $item['public_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                continue; 
            }
        }

        // Hapus folder temp setelah selesai
        // INI JUGA SUDAH SESUAI PERMINTAANMU: "ticket/tmp/..."
        $tmpUserFolder = "ticket/tmp/{$user->id}";
        if (Storage::disk('public')->exists($tmpUserFolder)) {
            Storage::disk('public')->deleteDirectory($tmpUserFolder);
            Log::info("Folder temp dihapus: {$tmpUserFolder}");
        }

        return response()->json(['ok' => true]);
    }
}