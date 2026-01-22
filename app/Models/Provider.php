<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'mode',
        'status',
        'config',
        'last_checked_at',
        'last_error_message',
    ];

    protected $casts = [
        'config' => 'array',
        'last_checked_at' => 'datetime',
    ];

    protected array $encryptedConfigKeys = [
        'client_id',
        'client_secret',
        'office_id',
    ];

    public static function defaults(): array
    {
        return [
            ['name' => 'Manual', 'code' => 'manual'],
            ['name' => 'Amadeus', 'code' => 'amadeus'],
            ['name' => 'Sabre', 'code' => 'sabre'],
            ['name' => 'Travelport (Galileo)', 'code' => 'travelport'],
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getConfigValue(string $key): ?string
    {
        $config = $this->config ?? [];
        return $config[$key] ?? null;
    }

    public function setConfigAttribute($value): void
    {
        $config = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : []);
        $config = is_array($config) ? $config : [];

        foreach ($this->encryptedConfigKeys as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $raw = $config[$key];
            if ($raw === null || $raw === '') {
                $config[$key] = null;
                continue;
            }

            if (! is_string($raw)) {
                continue;
            }

            // Avoid double encryption.
            try {
                Crypt::decryptString($raw);
                $config[$key] = $raw;
            } catch (\Throwable $e) {
                $config[$key] = Crypt::encryptString($raw);
            }
        }

        $this->attributes['config'] = json_encode($config);
    }

    public function getConfigAttribute($value): array
    {
        $config = is_array($value) ? $value : json_decode((string) $value, true);
        $config = is_array($config) ? $config : [];

        foreach ($this->encryptedConfigKeys as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $raw = $config[$key];
            if (! is_string($raw) || $raw === '') {
                continue;
            }

            try {
                $config[$key] = Crypt::decryptString($raw);
            } catch (\Throwable $e) {
                // Keep raw value if it's not encrypted.
            }
        }

        return $config;
    }
}
