<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id')
                ->constrained('reconciliation_runs')
                ->cascadeOnDelete();
            $table->foreignId('infaq_transaction_id')
                ->nullable()
                ->constrained('infaq_transactions')
                ->nullOnDelete();
            $table->string('external_transaction_id', 150)->nullable()->index();
            $table->string('external_order_id', 150)->nullable()->index();
            $table->decimal('external_amount', 15, 2)->nullable();
            $table->decimal('system_amount', 15, 2)->nullable();
            $table->string('external_acquirer', 100)->nullable();
            $table->string('match_status', 40)->index();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_items');
    }
};