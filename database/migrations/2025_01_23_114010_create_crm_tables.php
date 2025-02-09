<?php

use App\Models\Contact;
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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('New');
            $table->foreignIdFor(Contact::class)->nullable();
            $table->foreignIdFor(User::class);

            $table->timestamps();
        });
        Schema::create('calls', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('callable');
            $table->dateTime('date');
            $table->string('type')->nullable();
            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('calls');
    }
};
