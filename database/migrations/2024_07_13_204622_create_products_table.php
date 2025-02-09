<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');

            $table->string('type')->default('product')->nullable();
            $table->string('name');

            // Product Data
            $table->string('slug')->unique()->index();
            $table->string('sku')->nullable();

            // More Data
            $table->text('about')->nullable();
            $table->longText('description')->nullable();
            $table->text('details')->nullable();

            // Options
            $table->boolean('is_activated')->default(1)->nullable();
            $table->boolean('is_in_stock')->default(1)->nullable();
            $table->boolean('is_shipped')->default(0)->nullable();
            $table->boolean('is_trend')->default(0)->nullable();

            // Has Options
            $table->boolean('has_options')->default(0)->nullable();
            $table->boolean('has_multi_price')->default(0)->nullable();
            $table->boolean('has_unlimited_stock')->default(0)->nullable();
            $table->boolean('has_max_cart')->default(0)->nullable();
            $table->bigInteger('min_cart')->nullable()->unsigned();
            $table->bigInteger('max_cart')->nullable()->unsigned();
            $table->boolean('has_stock_alert')->default(0)->nullable();
            $table->bigInteger('min_stock_alert')->nullable()->unsigned();
            $table->bigInteger('max_stock_alert')->nullable()->unsigned();

            // Customize look
            $table->string('color')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
