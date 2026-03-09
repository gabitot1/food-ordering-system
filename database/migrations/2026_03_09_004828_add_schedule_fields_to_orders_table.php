<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_schedule')->default(false)->after('notes');
            $table->datetime('schedule_for')->nullable()->after('is_schedule');
            $table->string('schedule_slot')->nullable()->after('schedule_for');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_schedule','schedule_for','schedule_slot']);
        });
    }
};
