<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infaq_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('midtrans')->index();
            $table->string('provider_transaction_id', 150)->unique();
            $table->string('provider_order_id', 150)->nullable()->index();
            $table->string('merchant_id', 100)->nullable()->index();
            $table->decimal('gross_amount', 15, 2);
            $table->char('currency', 3)->default('IDR');
            $table->string('payment_type', 50)->nullable()->index();

            // 3 Dimensi Status Terpisah
            $table->string('transaction_status', 30)->index();
            $table->string('review_status', 30)->default('NOT_REVIEWED')->index();
            $table->string('reconciliation_status', 40)->default('NEEDS_REVIEW')->index();

            // Waktu & Metadata Cross-Issuer QRIS (Nullable karena bervariasi antar issuer)
            $table->timestampTz('transaction_time')->index();
            $table->timestampTz('settlement_time')->nullable()->index();
            $table->string('acquirer', 100)->nullable()->index();
            $table->string('issuer', 100)->nullable();
            $table->string('merchant_cross_reference_id', 150)->nullable()->index();

            $table->jsonb('raw_payload')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infaq_transactions');
    }
};