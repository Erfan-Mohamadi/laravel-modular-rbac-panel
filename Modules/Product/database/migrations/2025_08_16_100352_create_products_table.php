<?php
// Modules/Product/database/migrations/2025_08_16_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('price');
            $table->unsignedTinyInteger('discount')->default(0)->nullable();
            $table->enum('availability_status', ['coming_soon', 'available', 'unavailable'])->default('available');
            $table->boolean('status')->default(true); // active/inactive
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status', 'availability_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
