<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // $table->enum('pet_user_type', ['pet_owner', 'groomer', 'both'])->nullable()->after('user_type');
            // $table->string('full_name')->nullable()->after('email');
            // $table->string('address')->nullable()->after('photo');
            // $table->boolean('email_notification')->default(1)->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pet_user_type', 'full_name', 'address', 'email_notification']);
        });
    }
};
