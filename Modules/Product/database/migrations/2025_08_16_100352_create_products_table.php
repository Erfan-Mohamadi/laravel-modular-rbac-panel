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
            $table->unsignedBigInteger('price'); // Using integer for price in cents/minor units
            $table->unsignedTinyInteger('discount')->default(0)->nullable();
            $table->enum('availability_status', ['coming_soon', 'available', 'unavailable'])->default('available');
            $table->boolean('status')->default(true); // active/inactive
            $table->text('description')->nullable();

            // Foreign key columns
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // Indexes for better query performance
            $table->index(['status', 'availability_status']);
            $table->index('title');
            $table->index('brand_id');
            $table->index('category_id');
            $table->index('price');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
