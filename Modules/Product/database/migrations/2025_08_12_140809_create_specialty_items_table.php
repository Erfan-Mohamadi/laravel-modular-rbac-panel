<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('specialty_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained('specialties')->onDelete('cascade');
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('specialty_items');
    }
};
