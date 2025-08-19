<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_specialty', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('specialty_id');
            $table->unsignedInteger('special_item_id')->nullable();
            $table->text('value')->nullable(); // Store the specialty value for this product
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');

            // Unique constraint to prevent duplicate product-specialty pairs
            $table->unique(['product_id', 'specialty_id']);

            // Indexes for better performance
            $table->index('product_id');
            $table->index('specialty_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_specialty');
    }
};
