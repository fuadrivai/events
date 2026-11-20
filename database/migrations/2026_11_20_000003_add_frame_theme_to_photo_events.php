<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->string('frame_theme', 20)->default('classic')->after('frame_background_color');
        });
    }

    public function down(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->dropColumn('frame_theme');
        });
    }
};
