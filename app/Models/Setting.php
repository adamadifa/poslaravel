<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'display_name',
    ];

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("app_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set / Update a setting value by key.
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'string', ?string $displayName = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $value,
                'type' => $type,
                'display_name' => $displayName ?? $key,
            ]
        );

        Cache::forget("app_setting_{$key}");
        return $setting;
    }

    /**
     * Get all settings grouped by group.
     */
    public static function getAllGrouped()
    {
        return static::all()->groupBy('group');
    }
}
