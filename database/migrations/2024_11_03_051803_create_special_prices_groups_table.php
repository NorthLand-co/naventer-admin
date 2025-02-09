<?php

use App\Models\SpecialPricesGroup;
use App\Models\User;
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
        Schema::create('special_prices_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
        });

        Schema::create('user_has_special_prices_group', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(SpecialPricesGroup::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_prices_groups');
    }
};
