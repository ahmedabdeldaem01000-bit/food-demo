<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('paypal_capture_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['paypal_capture_id']);
            $table->dropColumn('paypal_capture_id');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->integer('price')->change();
        });
    }
};
