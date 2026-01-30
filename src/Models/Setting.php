<?php

namespace AdaReach\Sms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'adarearch_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'encrypted',
    ];

    protected $casts = [
        'encrypted' => 'boolean',
    ];

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("adarearch_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            $value = $setting->encrypted ? decrypt($setting->value) : $setting->value;

            // Cast to appropriate type
            switch ($setting->type) {
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    return (int) $value;
                case 'json':
                    return json_decode($value, true);
                default:
                    return $value;
            }
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, bool $encrypted = false): void
    {
        $setting = static::firstOrCreate(['key' => $key]);

        $setting->value = $encrypted ? encrypt($value) : $value;
        $setting->encrypted = $encrypted;
        $setting->save();

        Cache::forget("adarearch_setting_{$key}");
    }

    /**
     * Get all settings as array
     */
    public static function getAll(): array
    {
        return Cache::remember('adarearch_all_settings', 3600, function () {
            $settings = static::all();
            $result = [];

            foreach ($settings as $setting) {
                $value = $setting->encrypted ? '***********' : $setting->value;
                
                $result[$setting->key] = [
                    'value' => $value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'encrypted' => $setting->encrypted,
                ];
            }

            return $result;
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('adarearch_all_settings');
        
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("adarearch_setting_{$key}");
        }
    }
}
