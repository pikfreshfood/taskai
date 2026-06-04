<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAiDownload extends Model
{
    protected $table = 'taskai_downloads';

    protected $fillable = [
        'taskai_app_update_id',
        'source',
        'ip_hash',
        'user_agent',
        'referer',
    ];

    public function appUpdate(): BelongsTo
    {
        return $this->belongsTo(TaskAiAppUpdate::class, 'taskai_app_update_id');
    }
}
