<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->string('title')->nullable();

            $table->foreignId('province_id')
                ->constrained('provinces')
                ->cascadeOnDelete();
            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnDelete();

            $table->string('district')->nullable();
            $table->string('postal_code', 20);
            $table->string('address_line')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
