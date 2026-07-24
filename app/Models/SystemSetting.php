<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public static function get(string $key, $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        $data = ['value' => is_array($value) ? json_encode($value) : (string) $value, 'type' => $type];
        if ($description) $data['description'] = $description;

        static::updateOrCreate(['key' => $key], $data);
    }
}
