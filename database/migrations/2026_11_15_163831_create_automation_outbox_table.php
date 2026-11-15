<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('event_type', 100)->index();
            $table->string('aggregate_type', 100);
            $table->string('aggregate_id', 100);
            $table->jsonb('payload');
            $table->string('status', 30)->default('PENDING')->index(); // PENDING, PROCESSING, PROCESSED, FAILED
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->unsignedSmallInteger('max_retries')->default(5);
            $table->timestampTz('next_retry_at')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'next_retry_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_outbox');
    }
};