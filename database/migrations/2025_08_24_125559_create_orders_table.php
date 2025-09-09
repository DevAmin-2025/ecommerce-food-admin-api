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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained('user_addresses');
            $table->foreignId('coupon_id')->nullable()->constrained('discounts');
            $table->tinyInteger('status')->default(0);
            $table->unsignedInteger('total_amount');
            $table->unsignedInteger('coupon_amount')->default(0);
            $table->unsignedInteger('paying_amount');
            $table->boolean('payment_status')->default(0);
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
