<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('category_specialty', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('specialty_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->primary(['category_id', 'specialty_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_specialty');
    }
};
