<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Bersihkan data lama agar tidak melanggar enum baru
        DB::table('booking_rooms')
            ->where('booking_type', 'hybrid')
            ->update(['booking_type' => 'online_meeting']);

        DB::table('booking_rooms')
            ->where('booking_type', 'etc')
            ->update(['booking_type' => 'meeting']);

        // 2) Ubah definisi ENUM: hanya 'meeting' dan 'online_meeting'
        DB::statement("
            ALTER TABLE `booking_rooms`
            MODIFY `booking_type`
            ENUM('meeting','online_meeting')
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci
            NOT NULL
            DEFAULT 'meeting'
        ");
    }

    public function down(): void
    {
        // Kembalikan ke definisi semula (meeting | online_meeting | hybrid | etc)
        DB::statement("
            ALTER TABLE `booking_rooms`
            MODIFY `booking_type`
            ENUM('meeting','online_meeting','hybrid','etc')
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci
            NOT NULL
            DEFAULT 'meeting'
        ");

        // (Opsional) Tidak mengubah kembali nilai data karena tidak ada mapping pasti.
        // Silakan sesuaikan jika ingin mengembalikan 'hybrid' / 'etc' berdasarkan logika bisnis.
    }
};
