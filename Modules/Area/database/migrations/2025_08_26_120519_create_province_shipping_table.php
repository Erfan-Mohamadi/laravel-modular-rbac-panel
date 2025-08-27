<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('province_shipping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('shipping_id')->constrained('shipping');
            $table->unsignedInteger('price')->nullable();
            $table->timestamps();

            $table->unique(['province_id', 'shipping_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_shipping');
    }
};
