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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_id');

            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('price');

            // Virtual column (calculated on the fly, not stored)
            $table->unsignedBigInteger('total_price')->virtualAs('price * quantity');

            $table->timestamps();

            // Add constraints
            $table->foreign('product_id')
                ->references('id')
                ->on('products');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            // Ensure uniqueness
            $table->unique(['product_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
