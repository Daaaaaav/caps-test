<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AISettings extends Model
{
    protected $table    = 'ai_settings';
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    // ── Cache TTL (seconds) ──────────────────────────────────────────────────
    private const CACHE_KEY = 'ai_settings_all';
    private const CACHE_TTL = 3600; // 1 hour

    // ── Typed accessor ───────────────────────────────────────────────────────

    /**
     * Return the value cast to the declared type.
     */
    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'int'   => (int)   $this->value,
            'float' => (float) $this->value,
            'bool'  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            default => $this->value,
        };
    }

    // ── Static helpers ───────────────────────────────────────────────────────

    /**
     * Get one setting value by key, cast to its declared type.
     * Falls back to $default when the key is not found.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Return all settings for a group as  key => casted_value.
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->casted_value])
            ->toArray();
    }

    /**
     * Persist a single value and bust the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => (string) $value]);
        static::bustCache();
    }

    /**
     * Bust the settings cache (call after any bulk update).
     */
    public static function bustCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    /**
     * Load all settings from cache (or DB on miss).
     * Returns key => casted_value map.
     */
    private static function allCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::all()
                ->mapWithKeys(fn ($s) => [$s->key => $s->casted_value])
                ->toArray();
        });
    }
}
