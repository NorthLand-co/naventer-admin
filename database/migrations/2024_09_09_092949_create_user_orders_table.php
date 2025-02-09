<?php

use App\Models\ShippingVariant;
use App\Models\User;
use App\Models\UserAddress;
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
        Schema::create('user_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(UserAddress::class);
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('price_with_discount');
            $table->foreignIdFor(ShippingVariant::class);
            $table->unsignedBigInteger('shipment_price')->default(0);
            $table->string('coupon')->nullable();
            $table->unsignedBigInteger('coupon_price')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_orders');
    }
};
