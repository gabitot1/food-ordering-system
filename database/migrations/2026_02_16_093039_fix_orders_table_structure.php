<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {

            // Drop foreign key first if exists
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            $table->dropColumn([
                
                'delivery_fee',
                'payment_method',
                'payment_status'
            ]);
        });
    }

    public function down()
    {
        //
    }
};
