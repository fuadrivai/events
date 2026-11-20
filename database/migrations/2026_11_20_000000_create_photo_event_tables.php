<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->string('title', 160);
            $table->string('camera_label', 120);
            $table->string('subtitle', 255)->nullable();
            $table->string('frame_color', 7)->default('#D9A978');
            $table->unsignedTinyInteger('countdown_seconds')->default(3);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_event_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('token', 64)->unique();
            $table->string('path');
            $table->string('mime_type', 40);
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['photo_event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('photo_events');
    }
};
