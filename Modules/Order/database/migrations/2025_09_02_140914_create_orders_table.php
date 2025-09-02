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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('shipping_id')->constrained('shippings');

            $table->unsignedBigInteger('amount');
            $table->enum('status', ['new', 'wait_for_payment', 'in_progress', 'delivered', 'failed'])
                ->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
