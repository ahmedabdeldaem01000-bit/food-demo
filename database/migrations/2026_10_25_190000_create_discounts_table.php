<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 64)->unique();
            $table->text('description')->nullable();
            $table->string('type', 16);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->uuid('activation_token')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('discount_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->uuid('activation_token')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->unique(
                ['discount_id', 'activation_token', 'notifiable_id'],
                'notifications_discount_activation_user_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropUnique('notifications_discount_activation_user_unique');
            $table->dropColumn(['discount_id', 'activation_token', 'email_sent_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('discount_id');
            $table->dropColumn('discount_amount');
        });

        Schema::dropIfExists('discounts');
    }
};
