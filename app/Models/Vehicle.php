<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    /**
     * Properti $fillable DI-UPDATE.
     * - 'image' dihapus (sesuai migrasi 53).
     */
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'plate_number',
        'year',
        'notes',
        'is_active',
        // 'image', // <-- Dihapus
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}