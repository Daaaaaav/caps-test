<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_requirements', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        if (\DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', \DB::getDatabaseName())->where('TABLE_NAME', 'booking_requirements')->where('INDEX_NAME', 'booking_requirements_deleted_at_index')->exists()) {
            \DB::statement('ALTER TABLE `booking_requirements` DROP INDEX `booking_requirements_deleted_at_index`');
        }
        if (\DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', \DB::getDatabaseName())->where('TABLE_NAME', 'booking_requirements')->where('COLUMN_NAME', 'deleted_at')->exists()) {
            \DB::statement('ALTER TABLE `booking_requirements` DROP COLUMN `deleted_at`');
        }
    }
};
