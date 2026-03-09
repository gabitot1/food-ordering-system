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
        if (!Schema::hasColumn('orders', 'is_scheduled')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_scheduled')->default(false);
            });
        }

        if (!Schema::hasColumn('orders', 'scheduled_for')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dateTime('scheduled_for')->nullable();
            });
        }

        if (!Schema::hasColumn('orders', 'schedule_slot')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('schedule_slot')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('orders', 'is_scheduled')) {
                $drops[] = 'is_scheduled';
            }
            if (Schema::hasColumn('orders', 'scheduled_for')) {
                $drops[] = 'scheduled_for';
            }
            if (Schema::hasColumn('orders', 'schedule_slot')) {
                $drops[] = 'schedule_slot';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
