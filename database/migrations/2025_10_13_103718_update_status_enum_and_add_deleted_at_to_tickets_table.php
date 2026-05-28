<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });

        DB::statement("ALTER TABLE tickets MODIFY status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED') DEFAULT 'OPEN'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY status ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED', 'DELETED') DEFAULT 'OPEN'");
        if (DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'tickets')->where('INDEX_NAME', 'tickets_deleted_at_index')->exists()) {
            DB::statement('ALTER TABLE `tickets` DROP INDEX `tickets_deleted_at_index`');
        }
        if (DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', DB::getDatabaseName())->where('TABLE_NAME', 'tickets')->where('COLUMN_NAME', 'deleted_at')->exists()) {
            DB::statement('ALTER TABLE `tickets` DROP COLUMN `deleted_at`');
        }
    }
};
