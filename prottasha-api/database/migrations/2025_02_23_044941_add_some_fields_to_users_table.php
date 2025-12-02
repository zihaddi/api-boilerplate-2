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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('email_notification')->default(1)->after('email_verified_at');
            $table->enum('accessibility_statement', ['team_email','site_email'])->default('team_email')->after('email_notification');
            $table->enum('compliance_statement', ['team_email','site_email'])->default('team_email')->after('accessibility_statement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
