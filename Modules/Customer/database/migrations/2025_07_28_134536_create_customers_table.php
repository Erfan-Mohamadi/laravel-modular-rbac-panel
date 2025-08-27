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
            $table->string('name', 100)->nullable();   // optional, since you login via mobile
            $table->string('email', 191)->unique()->nullable();
            $table->string('mobile', 20)->unique();    // login identifier
            $table->timestamp('mobile_verified_at')->nullable(); // OTP verified
            $table->string('password', 191)->nullable(); // optional if only OTP login
            $table->string('last_login_date', 191)->nullable();
            $table->boolean('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });

        // addresses table (one-to-many relation with customers)
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete(); // if customer is deleted, delete addresses
            $table->string('title')->nullable(); // e.g. Home, Office
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('address_line')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('customers');
    }
};
