<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfaqCategory extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'target_amount',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'is_default'    => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(FundAllocation::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}