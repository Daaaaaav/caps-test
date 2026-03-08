<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// ✘ HAPUS BARIS INI:
// use Illuminate\Database\Eloquent\Concerns\HasUlids; 

class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;
    // ✘ HAPUS BARIS INI:
    // use HasUlids; 

    protected $table = 'tickets';
    protected $primaryKey = 'ticket_id';
    public $timestamps = true;

    /**
     * ✔ TAMBAHKAN BARIS INI
     * Beri tahu Eloquent bahwa Primary Key Anda adalah auto-incrementing integer.
     */
    public $incrementing = true;

    /**
     * Ini sudah benar, biarkan. 
     * Ini memberitahu Eloquent bahwa PK-nya adalah 'int'.
     */
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'company_id',
        'requestdept_id',
        'department_id',
        'user_id',
        'subject',
        'description',
        'priority',
        'status',
    ];

    /**
     * ✔ Ini sudah benar.
     * Method ini akan otomatis mengisi kolom 'ulid' yang TERPISAH
     * saat membuat tiket baru.
     */
    protected static function booted(): void
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ulid)) {
                $ticket->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * ✔ Ini juga sudah benar.
     * Ini memberitahu Laravel untuk menggunakan kolom 'ulid' (bukan 'ticket_id')
     * saat mencari tiket di URL (Route Model Binding).
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id', 'department_id');
    }

    public function requesterDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'requestdept_id', 'department_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(\App\Models\TicketAttachment::class, 'ticket_id', 'ticket_id');
    }

    public function latestAssignment()
    {
        return $this->hasOne(TicketAssignment::class, 'ticket_id', 'ticket_id')
            ->latestOfMany('assigned_at');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(TicketAssignment::class, 'ticket_id', 'ticket_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class, 'ticket_id', 'ticket_id');
    }

    public function getTitleAttribute()
    {
        return $this->subject;
    }

    public function getNotesAttribute()
    {
        return $this->description;
    }

    public function getIdAttribute()
    {
        return $this->ticket_id;
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\TicketAssignment::class, 'ticket_id', 'ticket_id');
    }

    // Method loadRecentComments() Anda tidak ada di file asli, 
    // jadi saya tidak sertakan di sini agar sesuai dengan file yang Anda berikan.
    // Jika Anda memilikinya, Anda bisa menambahkannya kembali.
}