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
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }
}