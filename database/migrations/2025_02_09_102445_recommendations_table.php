<?php

use App\Models\Product;
use App\Models\RecommendationAnswer;
use App\Models\RecommendationQuestion;
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

        // Questions Table
        Schema::create('recommendation_questions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('order')->default(255);
            $table->float('weight')->default(1.0);
            $table->timestamps();
        });

        // Answers Table
        Schema::create('recommendation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RecommendationQuestion::class);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('order')->default(255);
            $table->timestamps();
        });

        // Pivot Table for Product-Answer Association
        Schema::create('recommendation_product_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(RecommendationAnswer::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_product_answers');
        Schema::dropIfExists('recommendation_answers');
        Schema::dropIfExists('recommendation_questions');
    }
};
