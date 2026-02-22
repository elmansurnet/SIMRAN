<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 300, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            return $record?->value ?? $default;
        });
    }

    /**
     * Set (upsert) a setting value and bust cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    /**
     * How many extra days after a disbursement's end_date transactions are allowed.
     */
    public static function extraTransactionDays(): int
    {
        return (int) static::get('extra_transaction_days', 0);
    }
}
