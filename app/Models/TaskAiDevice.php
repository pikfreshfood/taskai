<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAiDevice extends Model
{
    protected $table = 'taskai_devices';

    protected $fillable = [
        'serial_hash',
        'serial_hint',
        'last_user_id',
        'total_usage_seconds',
        'free_usage_limit_seconds',
        'upgraded_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'total_usage_seconds' => 'float',
            'free_usage_limit_seconds' => 'integer',
            'upgraded_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function lastUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_user_id');
    }
}
