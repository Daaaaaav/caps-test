<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Kapasitas orang dalam satu ruangan.
            // Pakai unsignedSmallInteger agar hemat storage (0–65535).
            $table->unsignedSmallInteger('capacity')
                  ->nullable()
                  ->after('room_number'); // taruh setelah room_number
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
};
