<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM('pending', 'preparing', 'out_of_delivery', 'delivered', 'cancelled')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM('pending', 'preparing', 'ready', 'delivered', 'cancelled')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
