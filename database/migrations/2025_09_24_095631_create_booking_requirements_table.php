<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_requirements', function (Blueprint $table) {
            $table->foreignId('bookingroom_id')->constrained('booking_rooms', 'bookingroom_id')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('requirements', 'requirement_id')->cascadeOnDelete();
            $table->primary(['bookingroom_id', 'requirement_id']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requirements');
    }
};
