<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasColumn('events', 'price_question_label')) {
      Schema::table('events', function (Blueprint $table) {
        $table->string('price_question_label', 150)->nullable()->after('intro_html');
      });
    }
  }

  public function down(): void
  {
    if (Schema::hasColumn('events', 'price_question_label')) {
      Schema::table('events', function (Blueprint $table) {
        $table->dropColumn('price_question_label');
      });
    }
  }
};
