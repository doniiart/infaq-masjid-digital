<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationOutbox extends Model
{
    protected $table = 'automation_outbox';

    public const STATUS_PENDING    = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_PROCESSED  = 'PROCESSED';
    public const STATUS_FAILED     = 'FAILED';

    protected $fillable = [
        'event_id',
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'status',
        'retry_count',
        'max_retries',
        'next_retry_at',
        'processed_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'payload'       => 'array',
            'next_retry_at' => 'datetime',
            'processed_at'  => 'datetime',
        ];
    }
}