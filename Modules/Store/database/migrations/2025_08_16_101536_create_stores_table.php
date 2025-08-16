<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedInteger('balance')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stores');
    }
};
