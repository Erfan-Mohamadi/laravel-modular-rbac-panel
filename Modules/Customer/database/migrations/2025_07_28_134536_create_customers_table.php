<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('email', 191)->unique()->nullable();
            $table->string('mobile', 20)->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('password', 191)->nullable();
            $table->string('otp', 6)->nullable(); // Add OTP field
            $table->timestamp('otp_expires_at')->nullable(); // Add OTP expiry
            $table->string('last_login_date', 191)->nullable();
            $table->boolean('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
