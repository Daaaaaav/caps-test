<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/2025_09_29_055712_add_cloudinary_columns_to_ticket_attachments_table.php
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            // ganti baris uploaded_by yang sebelumnya pakai ->constrained()
            if (!Schema::hasColumn('ticket_attachments', 'uploaded_by')) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->after('file_type');
                $table->index('uploaded_by');
            }
            if (!Schema::hasColumn('ticket_attachments', 'cloudinary_public_id')) {
                $table->string('cloudinary_public_id')->nullable()->after('uploaded_by');
            }
            if (!Schema::hasColumn('ticket_attachments', 'bytes')) {
                $table->unsignedBigInteger('bytes')->default(0)->after('cloudinary_public_id');
            }
            if (!Schema::hasColumn('ticket_attachments', 'original_filename')) {
                $table->string('original_filename')->nullable()->after('bytes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_attachments', 'original_filename')) {
                $table->dropColumn('original_filename');
            }
            if (Schema::hasColumn('ticket_attachments', 'bytes')) {
                $table->dropColumn('bytes');
            }
            if (Schema::hasColumn('ticket_attachments', 'cloudinary_public_id')) {
                $table->dropColumn('cloudinary_public_id');
            }
            if (Schema::hasColumn('ticket_attachments', 'uploaded_by')) {
                $table->dropIndex(['uploaded_by']);
                $table->dropColumn('uploaded_by');
            }
        });
    }


};
