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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->foreignId('company_id')->constrained('companies', 'company_id')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments', 'department_id')->cascadeOnDelete();
            $table->foreignId('requestdept_id')->constrained('departments', 'department_id')->cascadeOnDelete(); 
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete(); 
            $table->string('subject');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED', 'DELETED'])->default('OPEN');
            $table->index(['company_id', 'status', 'priority']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
