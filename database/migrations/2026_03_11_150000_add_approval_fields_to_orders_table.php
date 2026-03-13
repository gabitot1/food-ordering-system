<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('status');
            }

            if (!Schema::hasColumn('orders', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('orders', 'approval_note')) {
                $table->text('approval_note')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            foreach (['approval_status', 'approved_at', 'approval_note'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
