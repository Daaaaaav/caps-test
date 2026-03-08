<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <--- DITAMBAHKAN
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = ['company_id', 'department_name'];

    public function getNameAttribute()
    {
        return $this->attributes['name']
            ?? $this->attributes['department_name']
            ?? null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    // <--- RELASI LAMA (TETAP DISIMPAN) --->
    /**
     * Mendapatkan user yang memiliki departemen ini sebagai departemen UTAMA (primary).
     * Kode lama Anda ($department->users) akan tetap berfungsi.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    // <--- RELASI BARU (UNTUK MULTI-DEPT) --->
    /**
     * Mendapatkan SEMUA user yang terhubung ke departemen ini via tabel pivot.
     * Saya beri nama 'allUsers' agar tidak bentrok dengan relasi 'users()' di atas.
     * Gunakan ini untuk fitur baru: $department->allUsers
     */
    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,          // Model tujuan
            'user_departments',   // Nama tabel pivot
            'department_id',      // Foreign key untuk Department di tabel pivot
            'user_id'             // Foreign key untuk User di tabel pivot
        );
    }
}