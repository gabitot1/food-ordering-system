<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {

            if (Schema::hasColumn('order_items', 'user_id')) {
                $table->dropForeign(['user_id']); // if foreign key exists
                $table->dropColumn('user_id');
            }

        });
    }

    public function down()
    {
        //
    }
};
