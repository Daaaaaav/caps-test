<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('information', function (Blueprint $table) {
            $table->foreignId('department_id')->after('company_id')->constrained('departments', 'department_id')->cascadeOnDelete();
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('information', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
        });
    }
};
