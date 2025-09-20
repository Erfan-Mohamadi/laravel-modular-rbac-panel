<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

            $table->foreignId('address_id')->nullable()->constrained('addresses');
            $table->text('formatted_address'); // Store complete formatted address
            $table->unsignedBigInteger('shipping_cost')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
