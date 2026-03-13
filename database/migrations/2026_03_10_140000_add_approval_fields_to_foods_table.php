<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            if (!Schema::hasColumn('foods', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('is_available');
            }

            if (!Schema::hasColumn('foods', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('foods', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('foods', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('foods', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            foreach (['approval_status', 'approved_at', 'rejection_reason'] as $column) {
                if (Schema::hasColumn('foods', $column)) {
                    $drops[] = $column;
                }
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
