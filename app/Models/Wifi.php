<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wifi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wifis';
    protected $primaryKey = 'wifi_id';

    protected $fillable = [
        'company_id',
        'ssid',
        'password',
        'location',
        'is_active',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }
}