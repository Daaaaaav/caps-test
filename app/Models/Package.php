<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.receptionist')]
#[Title('Package')]
class Package extends Model
{
    protected $table = 'packages';
    protected $primaryKey = 'package_id';

    protected $fillable = [
        'company_id',
        'receptionist_id',
        'package_name',
        'nama_pengirim',
        'nama_penerima',
        'penyimpanan',  
        'pengambilan',   
        'status',        
    ];

    protected $casts = [
        'pengambilan' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id', 'user_id');
    }
}
