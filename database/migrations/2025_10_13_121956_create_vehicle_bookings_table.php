<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus tabel lama jika ada, untuk memastikan skema bersih
        Schema::dropIfExists('vehicle_bookings');

        Schema::create('vehicle_bookings', function (Blueprint $table) {
            $table->id('vehiclebooking_id'); // Sesuai SQL: 'vehiclebooking_id'
            
            // Relasi ke tabel 'vehicles'
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->onDelete('cascade');
            
            $table->foreignId('company_id')->constrained('companies', 'company_id')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments', 'department_id')->onDelete('cascade');
            
            // user_id bisa null jika user dihapus, tapi booking tetap ada
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->onDelete('set null'); 
            
            $table->string('borrower_name')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('purpose');
            $table->string('destination')->nullable();

            // PERBAIKAN: Menggunakan enum dari file blade 'bookvehicle.blade.php' (tidak, ganjil, genap)
            // SQL lama kamu: enum('ya','tidak')
            $table->enum('odd_even_area', ['tidak', 'ganjil', 'genap'])->default('tidak');
            
            // Menggunakan 'purpose_type' dari SQL, sudah bagus
            // SQL lama kamu: enum('dinas','operasional','antar_jemput','lainnya')
            $table->enum('purpose_type', ['dinas', 'operasional', 'antar_jemput', 'lainnya'])->default('dinas');
            
            $table->boolean('terms_agreed')->default(false); // Sesuai SQL: 'terms_agreed'

            // =====================================================================
            // INI PERBAIKANNYA: Menambahkan kolom 'has_sim_a' yang hilang
            // =====================================================================
            $table->boolean('has_sim_a')->default(false);
            
            // PERBAIKAN: Kolom 'is_approve' dihapus karena redundant.
            // Kita akan gunakan 'status' untuk mengatur alur kerja.
            
            // PERBAIKAN: Menyesuaikan status enum dengan alur kerja yang diminta
            // 'pending' -> (User submit)
            // 'approved' -> (Admin approve, user bisa upload foto 'before')
            // 'on_progress' -> (User upload foto 'before' & pakai mobil, ini pengganti 'in_use')
            // 'returned' -> (User kembalikan mobil, harus upload foto 'after')
            // 'completed' -> (User sudah upload foto 'after', selesai)
            // 'rejected' -> (Admin tolak)
            // 'cancelled' -> (User batalkan)
            // SQL lama kamu: enum('pending','approved','in_use','returned','rejected','cancelled')
            $table->enum('status', [
                'pending', 
                'approved', 
                'on_progress', // Ini 'in_use' versi alur baru kamu
                'returned', 
                'completed', // Status baru untuk menandakan foto 'after' sudah diupload
                'rejected', 
                'cancelled'
            ])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Sesuai SQL, ada deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_bookings');
    }
};