<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('provider')->default('xendit');
            $table->string('event_type')->nullable();          // invoice.paid / invoice.expired, etc
            $table->string('external_id')->nullable()->index();
            $table->string('invoice_id')->nullable()->index();

            $table->boolean('verified')->default(false);
            $table->integer('http_status')->nullable();
            $table->string('handled_status')->nullable();      // e.g. "no-op", "processed", "ignored"
            $table->string('error_message')->nullable();

            $table->json('headers_json')->nullable();
            $table->json('payload_json')->nullable();

            $table->timestamps();

            $table->index(['provider', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
