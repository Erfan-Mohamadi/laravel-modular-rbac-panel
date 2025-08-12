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
        Schema::create('attribute_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('value'); // The option value (e.g., "Red", "Large", "Cotton")
            $table->timestamps();

            // Indexes
            $table->index('attribute_id');
            $table->unique(['attribute_id', 'value']); // Prevent duplicate values for same attribute
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_items');
    }
};
