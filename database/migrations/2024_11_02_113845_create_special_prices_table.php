<?php

use App\Models\ProductPrice;
use App\Models\SpecialPricesGroup;
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
        Schema::create('special_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductPrice::class);
            $table->foreignIdFor(SpecialPricesGroup::class);
            $table->foreignId('price_details')->constrained(table: 'product_prices')->nullable()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_prices');
    }
};
