<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 50)->index(); // midtrans_report, external_bank_statement
            $table->timestampTz('period_start');
            $table->timestampTz('period_end');
            $table->string('triggered_by_type', 30)->default('manual'); // n8n_cron, manual
            $table->foreignId('triggered_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('total_provider_records')->default(0);
            $table->unsignedInteger('total_system_records')->default(0);
            $table->unsignedInteger('total_matched')->default(0);
            $table->unsignedInteger('total_discrepancies')->default(0);
            $table->string('status', 30)->default('RUNNING')->index(); // RUNNING, COMPLETED, FAILED
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_runs');
    }
};