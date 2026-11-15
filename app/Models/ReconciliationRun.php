<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReconciliationRun extends Model
{
    protected $fillable = [
        'source_type',
        'period_start',
        'period_end',
        'triggered_by_type',
        'triggered_by_user_id',
        'total_provider_records',
        'total_system_records',
        'total_matched',
        'total_discrepancies',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end'   => 'datetime',
        ];
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }
}