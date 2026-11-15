<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infaq_transaction_id')
                ->constrained('infaq_transactions')
                ->cascadeOnDelete();
            $table->foreignId('infaq_category_id')
                ->constrained('infaq_categories')
                ->restrictOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->foreignId('allocated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['infaq_transaction_id', 'infaq_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_allocations');
    }
};