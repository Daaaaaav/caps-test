<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VehicleBookingPhoto extends Model
{
    use HasFactory;

    protected $table = 'vehicle_booking_photos';
    public $timestamps = true; // Migrasi baru menambahkan timestamps

    /**
     * Properti $fillable DI-UPDATE.
     * - 'photo_url' dan 'cloudinary_public_id' dihapus.
     * - 'photo_path' ditambahkan untuk local storage.
     */
    protected $fillable = [
        'vehiclebooking_id',
        'user_id',
        'photo_type',
        'photo_path', // <-- Ganti ini
    ];

    public function booking()
    {
        return $this->belongsTo(\App\Models\VehicleBooking::class, 'vehiclebooking_id', 'vehiclebooking_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
    }
}