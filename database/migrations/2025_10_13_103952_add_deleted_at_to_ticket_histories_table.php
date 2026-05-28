<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        if (\DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', \DB::getDatabaseName())->where('TABLE_NAME', 'ticket_histories')->where('INDEX_NAME', 'ticket_histories_deleted_at_index')->exists()) {
            \DB::statement('ALTER TABLE `ticket_histories` DROP INDEX `ticket_histories_deleted_at_index`');
        }
        if (\DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', \DB::getDatabaseName())->where('TABLE_NAME', 'ticket_histories')->where('COLUMN_NAME', 'deleted_at')->exists()) {
            \DB::statement('ALTER TABLE `ticket_histories` DROP COLUMN `deleted_at`');
        }
    }
};
