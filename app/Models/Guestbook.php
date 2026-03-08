<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Guestbook extends Model

{
    use SoftDeletes;
    protected $table = 'guestbooks';      
    protected $primaryKey = 'guestbook_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'department_id',
        'user_id',
        'date',
        'jam_in',
        'jam_out',
        'name',
        'phone_number',
        'instansi',
        'keperluan',
        'petugas_penjaga',
        'created_at', // If you explicitly set these, though usually handled by timestamps
        'updated_at',
    ];

    // If column `date` is DATE, this is safe. Times are left as string (TIME cast is not native Carbon).
    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    /** Scope: by company (including null company) */
    public function scopeForCompany(Builder $q, $companyId): Builder
    {
        return $q->where('company_id', $companyId);
    }

    /** Scope: fulltext-ish search */
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term)
            return $q;
        $like = '%' . $term . '%';

        return $q->where(function ($w) use ($like) {
            $w->where('name', 'like', $like)
                ->orWhere('phone_number', 'like', $like)
                ->orWhere('instansi', 'like', $like)
                ->orWhere('keperluan', 'like', $like)
                ->orWhere('petugas_penjaga', 'like', $like);
        });
    }
}
