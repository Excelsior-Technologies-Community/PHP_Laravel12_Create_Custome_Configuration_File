<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingHistory extends Model
{
    protected $fillable = [
        'setting_id',
        'old_value',
        'new_value',
        'changed_by',
        'action',
        'meta',
        'is_sensitive',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_sensitive' => 'boolean',
        ];
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }

    public function getDisplayOldValueAttribute(): string
    {
        return $this->is_sensitive
            ? '••••••••••••'
            : ($this->old_value ?? '—');
    }

    public function getDisplayNewValueAttribute(): string
    {
        return $this->is_sensitive
            ? '••••••••••••'
            : ($this->new_value ?? '—');
    }
}