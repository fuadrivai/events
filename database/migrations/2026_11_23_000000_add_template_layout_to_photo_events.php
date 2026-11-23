<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->string('template_path')->nullable();
            $table->json('frame_layout')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->dropColumn(['template_path', 'frame_layout']);
        });
    }
};
