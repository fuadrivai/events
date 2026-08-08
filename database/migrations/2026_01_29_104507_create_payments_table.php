<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
    $table->uuid('id')->primary();

    // ✅ HARUS bigint unsigned karena event_registrations.id bigint
    $table->unsignedBigInteger('registration_id');

    $table->string('provider')->default('xendit');
    $table->string('invoice_id')->unique();
    $table->string('external_id')->nullable()->index();
    $table->unsignedBigInteger('amount');
    $table->string('currency', 8)->default('IDR');

    $table->string('status', 24)->default('PENDING')->index();
    $table->timestamp('paid_at')->nullable();
    $table->text('invoice_url')->nullable();
    $table->json('payload_json')->nullable();
    $table->timestamps();

    // ✅ FK harus ke event_registrations
    $table->foreign('registration_id')
        ->references('id')
        ->on('event_registrations')
        ->cascadeOnDelete();

    $table->unique(['registration_id']);
    $table->index(['status', 'updated_at']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
