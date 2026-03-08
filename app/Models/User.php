<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <--- DITAMBAHKAN
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * department_id TETAP ADA di fillable untuk menjaga
     * kompatibilitas dengan kode lama Anda.
     */
    protected $fillable = [
        'company_id',
        'department_id', // <--- TETAP ADA
        'role_id',
        'full_name',
        'email',
        'phone_number',
        'password',
        'is_agent',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Simpan email lowercase.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn($value) => is_null($value) ? null : strtolower($value),
        );
    }

    /**
     * Virtual "name" -> full_name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->full_name,
            set: fn($value) => ['full_name' => $value],
        );
    }

    /**
     * Gunakan user_id sebagai auth identifier.
     */
    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    // ===== RELATIONS =====
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    // <--- RELASI LAMA (TETAP DISIMPAN) --->
    /**
     * Relasi untuk "Departemen Utama" user (dari kolom users.department_id).
     * Kode lama Anda ($user->department) akan tetap berfungsi.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // <--- RELASI BARU (UNTUK MULTI-DEPT) --->
    /**
     * Relasi untuk SEMUA departemen yang bisa diakses user (dari tabel pivot user_departments).
     * Gunakan ini untuk fitur baru: $user->departments
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,    // Model tujuan
            'user_departments',   // Nama tabel pivot
            'user_id',            // Foreign key untuk User di tabel pivot
            'department_id'       // Foreign key untuk Department di tabel pivot
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function commentReads(): HasMany
    {
        return $this->hasMany(TicketCommentRead::class, 'user_id', 'user_id');
    }

    // <--- FUNGSI HELPER BARU (Opsional tapi sangat berguna) --->
    /**
     * Helper untuk mengecek apakah user ada di departemen tertentu (via pivot).
     *
     * @param int $departmentId
     * @return bool
     */
    public function isInDepartment(int $departmentId): bool
    {
        // Cek di relasi 'departments' (jamak) apakah ada department_id yang cocok
        return $this->departments()->where('departments.department_id', $departmentId)->exists();
    }

    // <--- Relasi Users dengan tickets, ruang bookings, dan Vehicle bookings -->

    public function tickets()
    {
        return $this->hasMany(\App\Models\Ticket::class, 'user_id', 'user_id');
    }

    public function rooms()
    {
        return $this->hasMany(\App\Models\BookingRoom::class, 'user_id', 'user_id');
    }

    public function vehicles()
    {
        return $this->hasMany(\App\Models\VehicleBooking::class, 'user_id', 'user_id');
    }
}
