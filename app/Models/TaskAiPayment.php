<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAiPayment extends Model
{
    protected $table = 'taskai_payments';

    protected $fillable = [
        'user_id',
        'taskai_device_id',
        'reference',
        'amount',
        'currency',
        'plan_code',
        'plan_name',
        'duration_days',
        'status',
        'authorization_url',
        'paystack_data',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paystack_data' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(TaskAiDevice::class, 'taskai_device_id');
    }
}
