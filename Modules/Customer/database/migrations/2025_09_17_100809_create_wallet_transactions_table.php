<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');

            $table->enum('type', ['deposit', 'withdraw', 'refund']);
            $table->unsignedBigInteger('amount');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['wallet_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
