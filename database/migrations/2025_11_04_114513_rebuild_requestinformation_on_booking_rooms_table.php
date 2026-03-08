<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old column if it exists
        if (Schema::hasColumn('booking_rooms', 'requestinformation')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->dropColumn('requestinformation');
            });
        }

        // Add new nullable enum
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->enum('requestinformation', ['request', 'inform'])
                  ->nullable()
                  ->after('is_approve');
        });
    }

    public function down(): void
    {
        // Revert: drop enum and restore a boolean (NOT NULL, default false)
        if (Schema::hasColumn('booking_rooms', 'requestinformation')) {
            Schema::table('booking_rooms', function (Blueprint $table) {
                $table->dropColumn('requestinformation');
            });
        }

        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->boolean('requestinformation')->default(false)->after('is_approve');
        });
    }
};
