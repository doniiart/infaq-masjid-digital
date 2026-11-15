<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfaqTransaction extends Model
{
    // Dimensi 1: Status Pembayaran
    public const STATUS_SETTLEMENT   = 'SETTLEMENT';
    public const STATUS_FAILURE      = 'FAILURE';
    public const STATUS_DENY         = 'DENY';
    public const STATUS_CANCEL       = 'CANCEL';
    public const STATUS_EXPIRE       = 'EXPIRE';
    public const STATUS_NEEDS_REVIEW = 'NEEDS_REVIEW';

    // Dimensi 2: Status Pemeriksaan Takmir
    public const REVIEW_NOT_REVIEWED = 'NOT_REVIEWED';
    public const REVIEW_VERIFIED     = 'VERIFIED';
    public const REVIEW_FLAGGED      = 'FLAGGED';

    // Dimensi 3: Status Rekonsiliasi
    public const RECON_MATCHED                    = 'MATCHED';
    public const RECON_MISSING_IN_DATABASE        = 'MISSING_IN_DATABASE';
    public const RECON_MISSING_IN_PROVIDER_REPORT = 'MISSING_IN_PROVIDER_REPORT';
    public const RECON_AMOUNT_MISMATCH            = 'AMOUNT_MISMATCH';
    public const RECON_DUPLICATE                  = 'DUPLICATE';
    public const RECON_OUTSIDE_PROVIDER_SCOPE     = 'OUTSIDE_PROVIDER_SCOPE';
    public const RECON_NEEDS_REVIEW               = 'NEEDS_REVIEW';

    protected $fillable = [
        'provider',
        'provider_transaction_id',
        'provider_order_id',
        'merchant_id',
        'gross_amount',
        'currency',
        'payment_type',
        'transaction_status',
        'review_status',
        'reconciliation_status',
        'transaction_time',
        'settlement_time',
        'acquirer',
        'issuer',
        'merchant_cross_reference_id',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount'     => 'decimal:2',
            'transaction_time' => 'datetime',
            'settlement_time'  => 'datetime',
            'raw_payload'      => 'array',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(FundAllocation::class);
    }

    public function reconciliationItems(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }
}