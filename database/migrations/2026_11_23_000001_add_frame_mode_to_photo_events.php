<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->string('frame_mode', 20)->default('classic');
        });

        DB::table('photo_events')
            ->whereNotNull('template_path')
            ->update(['frame_mode' => 'template']);
    }

    public function down(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->dropColumn('frame_mode');
        });
    }
};
