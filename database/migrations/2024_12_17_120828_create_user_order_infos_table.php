<?php

use App\Models\UserOrder;
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
        Schema::create('user_order_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(UserOrder::class);
            $table->tinyInteger('type');
            $table->json('details')->nullable();
            $table->string('description')->nullable();
            $table->unique(['user_order_id', 'type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_order_infos');
    }
};
