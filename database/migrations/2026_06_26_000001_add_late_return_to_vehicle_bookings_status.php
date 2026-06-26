<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the status enum to include 'late_return'.
        // MySQL requires listing ALL existing values when modifying an enum column.
        DB::statement("
            ALTER TABLE vehicle_bookings
            MODIFY COLUMN status ENUM(
                'pending',
                'approved',
                'on_progress',
                'returned',
                'completed',
                'rejected',
                'cancelled',
                'late_return'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Move any late_return rows back to approved before shrinking the enum,
        // otherwise MySQL will reject the column modification.
        DB::statement("
            UPDATE vehicle_bookings SET status = 'approved' WHERE status = 'late_return'
        ");

        DB::statement("
            ALTER TABLE vehicle_bookings
            MODIFY COLUMN status ENUM(
                'pending',
                'approved',
                'on_progress',
                'returned',
                'completed',
                'rejected',
                'cancelled'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
