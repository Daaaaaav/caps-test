<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->string('online_meeting_event_id')->nullable()->after('online_meeting_password')
                  ->comment('Google Calendar event ID or Zoom meeting ID for cancellation');
        });
    }

    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropColumn('online_meeting_event_id');
        });
    }
};
