<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->string('frame_background_color', 7)->default('#54172F')->after('frame_color');
        });
    }

    public function down(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->dropColumn('frame_background_color');
        });
    }
};
