<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the function for email_phone_check
        DB::statement('
            CREATE OR REPLACE FUNCTION email_phone_check(email text, phone bigint) RETURNS integer AS $$
            BEGIN
                RETURN (CASE WHEN email IS NULL THEN 0 ELSE 1 END) +
                       (CASE WHEN phone IS NULL THEN 0 ELSE 1 END);
            END;
            $$ LANGUAGE plpgsql IMMUTABLE;
        ');

        // Modify the users table to add the new columns and the generated column
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('phone')->nullable()->unique()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->integer('email_phone_check')
                ->storedAs('email_phone_check(email, phone)');
            $table->index('email_phone_check', 'users_email_phone_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_phone_check');
            $table->dropColumn('email_phone_check');
            $table->dropColumn('phone_verified_at');
            $table->dropColumn('phone');
        });

        // Drop the function for email_phone_check
        DB::statement('DROP FUNCTION IF EXISTS email_phone_check(text, bigint)');
    }
};
