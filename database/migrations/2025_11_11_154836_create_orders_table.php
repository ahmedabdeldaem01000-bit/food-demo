<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
             $table->id();
    $table->foreignId('user_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('currency')->default('usd');
    $table->string('status')->default('pending');
    $table->string('stripe_session_id')->nullable();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
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
