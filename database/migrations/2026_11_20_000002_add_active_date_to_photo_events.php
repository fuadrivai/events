<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->date('active_date')->nullable()->after('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('photo_events', function (Blueprint $table) {
            $table->dropColumn('active_date');
        });
    }
};
