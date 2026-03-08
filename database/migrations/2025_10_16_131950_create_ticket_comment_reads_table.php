<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_comment_reads', function (Blueprint $table) {
            $table->id('read_id');
            $table->foreignId('comment_id')->constrained('ticket_comments', 'comment_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();

            $table->timestamp('read_at')->useCurrent();

            $table->timestamps();

            $table->unique(['comment_id', 'user_id'], 'uniq_comment_user');
            $table->index(['user_id', 'comment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comment_reads');
    }
};
