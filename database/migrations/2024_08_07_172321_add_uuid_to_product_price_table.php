<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->uuid()->unique();
        });
    }

    public function down()
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
