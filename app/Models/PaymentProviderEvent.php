<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProviderEvent extends Model
{
    protected $fillable = [
        'provider',
        'event_uuid',
        'provider_transaction_id',
        'provider_order_id',
        'signature_valid',
        'headers_json',
        'raw_payload',
        'processing_status',
        'error_message',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'headers_json'    => 'array',
            'raw_payload'     => 'array',
            'received_at'     => 'datetime',
        ];
    }
}