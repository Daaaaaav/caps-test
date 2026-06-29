<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guestbooks', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('qr_token', 64)->nullable()->unique()->after('email');
            // 'pending'  = QR sent, no scan yet (guest not yet confirmed as arrived)
            // 'ongoing'  = QR scanned at least once (first visitor recorded)
            // 'completed' = jam_out is set
            $table->enum('qr_status', ['pending', 'ongoing', 'completed'])
                  ->default('pending')
                  ->after('qr_token');
            $table->unsignedSmallInteger('visitor_count')
                  ->default(0)
                  ->comment('Number of times the QR code has been scanned (i.e. group size)')
                  ->after('qr_status');
        });
    }

    public function down(): void
    {
        Schema::table('guestbooks', function (Blueprint $table) {
            $table->dropColumn(['email', 'qr_token', 'qr_status', 'visitor_count']);
        });
    }
};
