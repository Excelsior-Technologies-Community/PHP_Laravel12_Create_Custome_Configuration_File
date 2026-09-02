<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'is_active',
        'is_sensitive',
        'environment_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_sensitive' => 'boolean',
            'options' => 'array',
        ];
    }

    public function getValueAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        /*
         * Sensitive values are encrypted in the database.
         * Decrypt only when the application actually reads the value.
         */
        if ($this->is_sensitive) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if ($this->type === 'json' && $value) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        /*
         * Controller handles encryption because is_sensitive may be
         * changed at the same time as value.
         */
        if ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SettingHistory::class);
    }

    /**
     * Return a masked value for dashboard display.
     */
    public function getMaskedValueAttribute(): string
    {
        if (!$this->is_sensitive) {
            $value = $this->value;

            if (is_array($value)) {
                return json_encode($value);
            }

            return (string) $value;
        }

        return '••••••••••••';
    }
}