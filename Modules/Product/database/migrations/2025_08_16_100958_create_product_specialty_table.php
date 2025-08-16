<?php
// Modules/Product/database/migrations/2025_08_16_create_product_specialty_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_specialty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('specialty_id')->constrained('specialties')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'specialty_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_specialty');
    }
};
