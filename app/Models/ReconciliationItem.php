<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_run_id',
        'infaq_transaction_id',
        'external_transaction_id',
        'external_order_id',
        'external_amount',
        'system_amount',
        'external_acquirer',
        'match_status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'external_amount' => 'decimal:2',
            'system_amount'   => 'decimal:2',
            'resolved_at'     => 'datetime',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class, 'reconciliation_run_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InfaqTransaction::class, 'infaq_transaction_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}