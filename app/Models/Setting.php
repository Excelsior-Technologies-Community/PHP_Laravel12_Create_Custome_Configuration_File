<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'environment_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'options' => 'array',
        ];
    }

    public function getValueAttribute($value)
    {
        if ($this->type === 'json' && $value) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->type === 'json' && $value) {
            $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
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
}