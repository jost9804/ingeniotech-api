<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    protected $fillable = [
        'client_name',
        'client_phone',
        'device_type',
        'problem_description',
        'status',
        'assigned_to',
        'progress',
        'price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
