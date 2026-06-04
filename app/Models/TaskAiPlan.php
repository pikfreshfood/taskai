<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAiPlan extends Model
{
    protected $table = 'taskai_plans';

    protected $fillable = [
        'code',
        'name',
        'price',
        'duration_days',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
