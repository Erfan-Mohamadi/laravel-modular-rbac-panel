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

            // Foreign keys without cascade
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('specialty_id')->constrained('specialties')->onDelete('restrict');

            // Optional specialty item
            $table->foreignId('specialty_item_id')->nullable()->constrained('specialty_items')->onDelete('set null');

            // Store value for text specialties
            $table->text('value')->nullable();

            $table->timestamps();

            // Unique combination
            $table->unique(['product_id', 'specialty_id']);

            // Indexes for performance
            $table->index(['product_id', 'specialty_id']);
            $table->index('specialty_item_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_specialty');
    }
};
