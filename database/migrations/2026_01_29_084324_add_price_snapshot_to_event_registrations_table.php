<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('event_registrations', function (Blueprint $table) {
      $table->unsignedBigInteger('price_option_id')->nullable()->after('event_id');
      $table->string('price_name_snapshot', 120)->nullable()->after('price_option_id');
      $table->unsignedBigInteger('amount_snapshot')->default(0)->after('price_name_snapshot');

      $table->index(['event_id', 'price_option_id']);
    });
  }

  public function down(): void
  {
    Schema::table('event_registrations', function (Blueprint $table) {
      $table->dropIndex(['event_id', 'price_option_id']);
      $table->dropColumn(['price_option_id', 'price_name_snapshot', 'amount_snapshot']);
    });
  }
};
