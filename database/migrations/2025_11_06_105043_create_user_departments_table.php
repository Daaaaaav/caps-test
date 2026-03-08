<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_departments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('cascade');

            // Primary key gabungan agar 1 user tidak bisa duplikat di 1 dept
            $table->primary(['user_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_departments');
    }
};