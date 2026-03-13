<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('foods', 'available_quantity')) {
            return;
        }

        Schema::table('foods', function (Blueprint $table) {
            $table->unsignedInteger('available_quantity')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('foods', 'available_quantity')) {
            return;
        }

        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn('available_quantity');
        });
    }
};
