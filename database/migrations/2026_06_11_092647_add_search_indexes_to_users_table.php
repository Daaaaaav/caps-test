<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add indexes on columns used in the receptionist users search query.
     * This speeds up LIKE '%...%' prefix scans and the role/company joins.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Used in WHERE full_name LIKE '%...%'
            $table->index('full_name', 'users_full_name_index');

            // phone_number LIKE searches
            $table->index('phone_number', 'users_phone_number_index');

            // Frequently filtered together in the receptionist query
            $table->index(['company_id', 'role_id'], 'users_company_role_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_full_name_index');
            $table->dropIndex('users_phone_number_index');
            $table->dropIndex('users_company_role_index');
        });
    }
};
