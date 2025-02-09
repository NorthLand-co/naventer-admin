<?php

use App\Models\ShippingVariant;
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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('tracking_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained();
            $table->string('name');
            $table->unsignedInteger('base_price');
            $table->boolean('is_default');
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ShippingVariant::class);
            $table->bigInteger('additional_price');
            $table->json('conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_variants');
        Schema::dropIfExists('shipping_rates');
    }
};
