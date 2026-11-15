<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundAllocation extends Model
{
    protected $fillable = [
        'infaq_transaction_id',
        'infaq_category_id',
        'allocated_amount',
        'allocated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InfaqTransaction::class, 'infaq_transaction_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InfaqCategory::class, 'infaq_category_id');
    }

    public function allocator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}