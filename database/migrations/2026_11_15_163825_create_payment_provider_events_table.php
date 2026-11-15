<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_provider_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('midtrans')->index();
            $table->uuid('event_uuid')->unique();
            $table->string('provider_transaction_id', 150)->nullable()->index();
            $table->string('provider_order_id', 150)->nullable()->index();
            $table->boolean('signature_valid')->default(false)->index();
            $table->jsonb('headers_json')->nullable();
            $table->jsonb('raw_payload');
            $table->string('processing_status', 40)->default('RECEIVED')->index();
            $table->text('error_message')->nullable();
            $table->timestampTz('received_at')->useCurrent();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_provider_events');
    }
};