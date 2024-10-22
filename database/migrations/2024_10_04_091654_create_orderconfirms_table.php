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
        Schema::create('orderconfirms', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_contact');
            $table->string('customer_email')->nullable();
            $table->string('invoice_number');
            $table->string('payment_mode');
            $table->string('coach_berth');
            $table->string('train');
            $table->string('delivery_station');
            $table->string('item_description');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('gst', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderconfirms');
    }
};
