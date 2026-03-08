<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Information extends Model
{
    use SoftDeletes;

    protected $table = 'information';
    protected $primaryKey = 'information_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'department_id',   // NEW
        'description',
        'event_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
        ];
    }

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // Optional accessors used in your Blade
    protected function formattedEventDate(): Attribute
    {
        return Attribute::get(fn() => $this->event_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'));
    }

    protected function formattedCreatedDate(): Attribute
    {
        return Attribute::get(fn() => $this->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'));
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForDepartment($query, ?int $departmentId)
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->whereNull('department_id');
            if (!is_null($departmentId)) {
                $q->orWhere('department_id', $departmentId);
            }
        });
    }

    protected function descriptionHtml(): Attribute
    {
        return Attribute::get(function () {
            $text = e((string) $this->description);
            $text = preg_replace(
                '~(https?://[^\s<]+)~i',
                '<a href="$1" target="_blank" rel="noopener" class="text-blue-700 underline">$1</a>',
                $text
            );
            return nl2br($text);
        });
    }
}
