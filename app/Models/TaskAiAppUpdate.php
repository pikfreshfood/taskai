<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAiAppUpdate extends Model
{
    protected $table = 'taskai_app_updates';

    protected $fillable = [
        'version',
        'download_url',
        'release_notes',
        'is_active',
        'is_required',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_required' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
